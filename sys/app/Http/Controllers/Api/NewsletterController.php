<?php

namespace App\Http\Controllers\Api;

use App\Enums\NewsletterStatus;
use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $data['email']]);
        $subscriber->name = $data['name'] ?? $subscriber->name;
        $subscriber->status = NewsletterStatus::Active;
        $subscriber->subscribed_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        return response()->json(['message' => 'Suscripción confirmada.']);
    }
}
