<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_hero_panel_settings', function (Blueprint $table) {
            $table->string('media_type')->default('none')->after('quote_footer');
            $table->string('image_path')->nullable()->after('media_type');
            $table->string('video_path')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('home_hero_panel_settings', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'image_path', 'video_path']);
        });
    }
};
