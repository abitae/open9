<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_hero_showcase_settings', function (Blueprint $table) {
            $table->id();
            $table->string('badge_label')->nullable();
            $table->timestamps();
        });

        Schema::create('home_hero_showcase_cards', function (Blueprint $table) {
            $table->id();
            $table->string('layout')->default('compact');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('media_type')->nullable();
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_hero_showcase_cards');
        Schema::dropIfExists('home_hero_showcase_settings');
    }
};
