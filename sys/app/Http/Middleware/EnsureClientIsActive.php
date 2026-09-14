<?php

namespace App\Http\Middleware;

use App\Enums\RecordStatus;
use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientIsActive
{
    /**
     * Corta el acceso de un Client suspendido/inactivo aunque conserve un
     * token válido emitido antes de la suspensión (login normal, Google u
     * otra vía). El token usado se revoca en el momento.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof Client && $user->status !== RecordStatus::Active) {
            $user->currentAccessToken()->delete();

            abort(403, 'Tu cuenta está inactiva. Contáctanos para reactivarla.');
        }

        return $next($request);
    }
}
