<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Mail\ClientGooglePasswordHintMail;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClientPasswordResetController extends Controller
{
    public function forgot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $client = Client::query()->where('email', $data['email'])->first();

        if ($client !== null && $client->status === RecordStatus::Active) {
            try {
                if ($client->password === null) {
                    Mail::to($client->email)->send(new ClientGooglePasswordHintMail($client));
                } else {
                    Password::broker('clients')->sendResetLink([
                        'email' => $client->email,
                    ]);
                }
            } catch (Throwable $exception) {
                Log::error('No se pudo enviar el correo de recuperación de contraseña.', [
                    'email' => $client->email,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Si el correo está registrado, te enviamos instrucciones.',
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', PasswordRule::default(), 'confirmed'],
        ]);

        $status = Password::broker('clients')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Client $client, string $password): void {
                if ($client->status !== RecordStatus::Active) {
                    throw ValidationException::withMessages([
                        'email' => 'El enlace no es válido o ha expirado.',
                    ]);
                }

                $client->forceFill([
                    'password' => $password,
                    'email_verified_at' => $client->email_verified_at ?? now(),
                ])->save();

                $client->tokens()->delete();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'El enlace no es válido o ha expirado.',
            ]);
        }

        return response()->json([
            'message' => 'Tu contraseña se actualizó. Ya puedes ingresar.',
        ]);
    }
}
