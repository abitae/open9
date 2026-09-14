<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('clients', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        $client = Client::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
            'email_verified_at' => null,
            'last_login_at' => now(),
        ]);

        return $this->respondWithToken($client, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $client = Client::query()->where('email', $data['email'])->first();

        if ($client === null || $client->password === null || ! Hash::check($data['password'], $client->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        if ($client->status !== RecordStatus::Active) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está inactiva. Contáctanos para reactivarla.',
            ]);
        }

        $client->forceFill(['last_login_at' => now()])->save();

        return $this->respondWithToken($client);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        return response()->json(['client' => $this->clientPayload($client)]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $client->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    private function respondWithToken(Client $client, int $status = 200): JsonResponse
    {
        $token = $client->createToken('spa')->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => $this->clientPayload($client),
        ], $status);
    }

    /**
     * @return array<string, mixed>
     */
    private function clientPayload(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'avatar' => $client->avatar,
            'email_verified' => $client->email_verified_at !== null,
        ];
    }
}
