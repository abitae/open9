<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SocialLoginSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    private const OAUTH_STATE_COOKIE = 'oauth_state';

    /**
     * Redirige a Google si el módulo está habilitado y configurado.
     * Acepta ?return_to= para devolver al frontend correcto tras el login
     * (p. ej. localhost:3002 en dev aunque FRONTEND_URL apunte a otro puerto).
     */
    public function redirect(Request $request): SymfonyRedirectResponse|RedirectResponse
    {
        $returnTo = $this->resolveReturnTo($request->query('return_to'));

        $settings = SocialLoginSetting::current();

        if (! $settings->googleEnabled()) {
            return redirect($this->frontendUrl('/ingresar?error=google_disabled', $returnTo));
        }

        $this->configureDriver($settings);

        $state = Str::random(40);
        Cache::put($this->oauthCacheKey($state), $returnTo, now()->addMinutes(10));

        $response = $this->googleDriver()->with(['state' => $state])->redirect();
        $response->headers->setCookie($this->stateCookie($state));

        return $response;
    }

    /**
     * Recibe el callback de Google, crea/busca el Client y emite un token.
     *
     * El `state` recibido debe coincidir con el que se cacheó en `redirect()`
     * y con el que se guardó en la cookie del navegador que inició el flujo.
     * Cualquier state ausente, desconocido, vencido, ya consumido o que no
     * coincida con la cookie aborta el login sin crear cliente ni token
     * (evita confusión de sesión / login CSRF).
     */
    public function callback(Request $request): RedirectResponse
    {
        $rawState = $request->query('state');
        $state = is_string($rawState) ? $rawState : '';

        $rawCookieState = $request->cookie(self::OAUTH_STATE_COOKIE);
        $cookieState = is_string($rawCookieState) ? $rawCookieState : '';

        $returnTo = $this->pullReturnTo($state, $cookieState);

        if ($returnTo === null) {
            Log::warning('Callback de Google con state inválido.', ['has_state' => $state !== '', 'has_cookie' => $cookieState !== '']);

            return $this->forgetStateCookie(
                redirect($this->frontendUrl('/ingresar?error=google_state_invalid', $this->defaultFrontendBase()))
            );
        }

        $settings = SocialLoginSetting::current();

        if (! $settings->googleEnabled()) {
            return $this->forgetStateCookie(redirect($this->frontendUrl('/ingresar?error=google_disabled', $returnTo)));
        }

        $this->configureDriver($settings);

        $providerError = $request->query('error');

        if (is_string($providerError) && $providerError !== '') {
            $errorCode = $providerError === 'access_denied' ? 'google_denied' : 'google_failed';

            return $this->forgetStateCookie(redirect($this->frontendUrl('/ingresar?error='.$errorCode, $returnTo)));
        }

        try {
            $googleUser = $this->googleDriver()->user();
        } catch (\Throwable $exception) {
            Log::warning('Fallo en el callback de Google.', ['error' => $exception->getMessage()]);

            return $this->forgetStateCookie(redirect($this->frontendUrl('/ingresar?error=google_failed', $returnTo)));
        }

        $email = $googleUser->getEmail();

        if ($email === null || $email === '') {
            return $this->forgetStateCookie(redirect($this->frontendUrl('/ingresar?error=google_no_email', $returnTo)));
        }

        $client = Client::query()->where('google_id', $googleUser->getId())->first()
            ?? Client::query()->where('email', $email)->first();

        if ($client !== null && $client->status !== RecordStatus::Active) {
            return $this->forgetStateCookie(redirect($this->frontendUrl('/ingresar?error=account_suspended', $returnTo)));
        }

        if ($client === null) {
            $client = new Client;
            $client->email = $email;
            $client->password = null;
            $client->status = RecordStatus::Active;
        }

        $client->name = $client->name ?: ($googleUser->getName() ?: Str::before($email, '@'));
        $client->google_id = $googleUser->getId();
        $client->avatar = $client->avatar ?: $googleUser->getAvatar();

        if ($client->email_verified_at === null) {
            $client->email_verified_at = now();
        }

        $client->last_login_at = now();
        $client->save();

        $token = $client->createToken('google')->plainTextToken;

        return $this->forgetStateCookie(
            redirect($this->frontendUrl('/auth/callback?token='.urlencode($token), $returnTo))
        );
    }

    /**
     * Devuelve el driver de Google como proveedor OAuth2 en modo stateless
     * (la API no usa sesión, así que evitamos la verificación de estado).
     */
    private function googleDriver(): AbstractProvider
    {
        $driver = Socialite::driver('google');

        abort_unless($driver instanceof AbstractProvider, 500, 'El proveedor de Google no está disponible.');

        return $driver->stateless();
    }

    private function configureDriver(SocialLoginSetting $settings): void
    {
        config([
            'services.google.client_id' => $settings->resolvedGoogleClientId(),
            'services.google.client_secret' => $settings->resolvedGoogleClientSecret(),
            'services.google.redirect' => $this->oauthRedirectUri($settings),
        ]);
    }

    /**
     * Valida el `state` de forma estricta: debe existir, coincidir (comparación
     * segura frente a temporización) con la cookie del navegador iniciador y
     * corresponder a una entrada de caché aún no consumida (single-use vía
     * Cache::pull, la caducidad la impone el TTL de la entrada). Cualquier
     * fallo devuelve null y el caller debe abortar el login.
     */
    private function pullReturnTo(string $state, string $cookieState): ?string
    {
        if ($state === '' || $cookieState === '' || ! hash_equals($cookieState, $state)) {
            return null;
        }

        $cached = Cache::pull($this->oauthCacheKey($state));

        return is_string($cached) && $cached !== '' ? $cached : null;
    }

    private function stateCookie(string $state): SymfonyCookie
    {
        return Cookie::make(
            self::OAUTH_STATE_COOKIE,
            $state,
            10,
            '/',
            null,
            ! app()->environment('local'),
            true,
            false,
            'lax'
        );
    }

    private function forgetStateCookie(RedirectResponse $response): RedirectResponse
    {
        $response->headers->setCookie(Cookie::forget(self::OAUTH_STATE_COOKIE));

        return $response;
    }

    private function resolveReturnTo(mixed $returnTo): string
    {
        $default = $this->defaultFrontendBase();

        if (! is_string($returnTo) || $returnTo === '') {
            return $default;
        }

        $returnTo = rtrim($returnTo, '/');
        $parts = parse_url($returnTo);

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return $default;
        }

        if (app()->environment('local')) {
            if ($this->isLoopbackHost($returnTo)) {
                return $returnTo;
            }
        }

        foreach ($this->allowedFrontendBases() as $allowed) {
            if ($returnTo === $allowed || str_starts_with($returnTo, $allowed.'/')) {
                return $returnTo;
            }
        }

        return $default;
    }

    /**
     * @return list<string>
     */
    private function allowedFrontendBases(): array
    {
        $bases = [
            rtrim((string) config('app.frontend_url'), '/'),
            rtrim((string) config('app.url'), '/'),
        ];

        return array_values(array_unique(array_filter($bases, fn (string $base): bool => $base !== '')));
    }

    private function oauthRedirectUri(SocialLoginSetting $settings): string
    {
        $live = url('/api/auth/google/callback');
        $configured = $settings->google_redirect_url;

        if (! is_string($configured) || $configured === '') {
            return $live;
        }

        if (app()->isProduction() && $this->isLoopbackHost($configured)) {
            return $live;
        }

        return $configured;
    }

    private function defaultFrontendBase(): string
    {
        $frontend = rtrim((string) config('app.frontend_url'), '/');
        $appUrl = rtrim((string) config('app.url'), '/');

        if (app()->environment('local')) {
            return $frontend !== '' ? $frontend : $appUrl;
        }

        if ($frontend !== '' && ! $this->isLoopbackHost($frontend)) {
            return $frontend;
        }

        return $appUrl !== '' ? $appUrl : $frontend;
    }

    private function isLoopbackHost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
    }

    private function frontendUrl(string $path, ?string $base = null): string
    {
        return rtrim($base ?? $this->defaultFrontendBase(), '/').'/'.ltrim($path, '/');
    }

    private function oauthCacheKey(string $state): string
    {
        return 'google_oauth_return:'.hash('sha256', $state);
    }
}
