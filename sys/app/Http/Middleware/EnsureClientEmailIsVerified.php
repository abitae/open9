<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientEmailIsVerified
{
    /**
     * Bloquea clientes autenticados cuyo correo aún no está confirmado.
     * Si no hay sesión Sanctum (checkout invitado) deja pasar la petición.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? $request->user('sanctum');

        if ($user instanceof Client && ! $user->hasVerifiedEmail()) {
            abort(403, 'Debes confirmar tu correo para continuar.');
        }

        return $next($request);
    }
}
