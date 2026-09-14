<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        return response()->json(['client' => $this->payload($client)]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($client->getKey())],
            'phone' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        if (! empty($data['password'])) {
            if ($client->password !== null && ! Hash::check($data['current_password'] ?? '', $client->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'La contraseña actual no es correcta.',
                ]);
            }

            $client->password = $data['password'];
        }

        $client->name = $data['name'];
        $client->email = $data['email'];
        $client->phone = $data['phone'] ?? null;
        $client->save();

        return response()->json(['client' => $this->payload($client)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'avatar' => $client->avatar,
            'email_verified' => $client->email_verified_at !== null,
            'has_password' => $client->password !== null,
            'created_at' => $client->created_at?->toIso8601String(),
        ];
    }
}
