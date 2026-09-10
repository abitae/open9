<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $addresses = $client->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ClientAddress $address): array => $this->payload($address))
            ->all();

        return response()->json(['addresses' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $data = $this->validated($request);

        $address = new ClientAddress($data);
        $address->client_id = $client->getKey();
        $address->is_default = (bool) ($data['is_default'] ?? false);

        if ($client->addresses()->count() === 0) {
            $address->is_default = true;
        }

        $address->save();

        if ($address->is_default) {
            $this->clearOtherDefaults($client, $address->id);
        }

        return response()->json(['address' => $this->payload($address)], 201);
    }

    public function update(Request $request, string $address): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $model = $client->addresses()->findOrFail($address);

        $data = $this->validated($request);
        $model->fill($data);
        $model->is_default = (bool) ($data['is_default'] ?? $model->is_default);
        $model->save();

        if ($model->is_default) {
            $this->clearOtherDefaults($client, $model->id);
        }

        return response()->json(['address' => $this->payload($model)]);
    }

    public function destroy(Request $request, string $address): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $model = $client->addresses()->findOrFail($address);
        $wasDefault = $model->is_default;
        $model->delete();

        if ($wasDefault) {
            $next = $client->addresses()->orderByDesc('id')->first();
            $next?->forceFill(['is_default' => true])->save();
        }

        return response()->json(['message' => 'Dirección eliminada.']);
    }

    public function setDefault(Request $request, string $address): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $model = $client->addresses()->findOrFail($address);
        $model->forceFill(['is_default' => true])->save();
        $this->clearOtherDefaults($client, $model->id);

        return response()->json(['address' => $this->payload($model)]);
    }

    private function clearOtherDefaults(Client $client, int $keepId): void
    {
        DB::table('client_addresses')
            ->where('client_id', $client->getKey())
            ->where('id', '!=', $keepId)
            ->update(['is_default' => false]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:2'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ClientAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'line1' => $address->line1,
            'line2' => $address->line2,
            'city' => $address->city,
            'region' => $address->region,
            'country' => $address->country,
            'postal_code' => $address->postal_code,
            'is_default' => (bool) $address->is_default,
        ];
    }
}
