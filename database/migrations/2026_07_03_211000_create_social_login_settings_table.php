<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_login_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('google_enabled')->default(false);
            $table->string('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            $table->string('google_redirect_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_login_settings');
    }
};
