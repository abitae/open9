<?php

use App\Enums\NewsletterStatus;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('subscribes a new email to the newsletter', function (): void {
    $this->postJson('/api/newsletter/subscribe', ['email' => 'lead@example.com', 'name' => 'Grace'])
        ->assertOk();

    $this->assertDatabaseHas('newsletter_subscribers', [
        'email' => 'lead@example.com',
        'name' => 'Grace',
        'status' => 'active',
    ]);
});

it('resubscribes a previously unsubscribed email without duplicating it', function (): void {
    NewsletterSubscriber::query()->create([
        'email' => 'lead@example.com',
        'status' => NewsletterStatus::Unsubscribed,
        'unsubscribed_at' => now(),
    ]);

    $this->postJson('/api/newsletter/subscribe', ['email' => 'lead@example.com'])->assertOk();

    $this->assertDatabaseCount('newsletter_subscribers', 1);
    $this->assertDatabaseHas('newsletter_subscribers', [
        'email' => 'lead@example.com',
        'status' => 'active',
        'unsubscribed_at' => null,
    ]);
});

it('rejects an invalid email', function (): void {
    $this->postJson('/api/newsletter/subscribe', ['email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
