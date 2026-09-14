<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_chat_settings', function (Blueprint $table) {
            $table->string('provider')->default('gemini')->after('is_enabled');
            $table->string('openai_api_key')->nullable()->after('api_key');
        });
    }

    public function down(): void
    {
        Schema::table('ai_chat_settings', function (Blueprint $table) {
            $table->dropColumn(['provider', 'openai_api_key']);
        });
    }
};
