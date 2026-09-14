<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('mercadopago');
            $table->boolean('is_enabled')->default(false);
            $table->string('mode')->default('sandbox');
            $table->string('currency', 3)->default('PEN');
            $table->text('public_key')->nullable();
            $table->text('access_token')->nullable();
            $table->text('sandbox_public_key')->nullable();
            $table->text('sandbox_access_token')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('statement_descriptor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
