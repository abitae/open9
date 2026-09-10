<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_hero_panel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('badge_label')->nullable();
            $table->string('headline_pre')->nullable();
            $table->string('headline_highlight')->nullable();
            $table->string('headline_subtitle')->nullable();
            $table->string('headline_subtitle_highlight')->nullable();
            $table->boolean('show_site_name_chip')->default(true);
            $table->text('description')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('cta_icon')->nullable();
            $table->string('quote_kicker')->nullable();
            $table->string('quote_primary')->nullable();
            $table->string('quote_secondary')->nullable();
            $table->string('quote_footer')->nullable();
            $table->timestamps();
        });

        Schema::create('home_hero_panel_stats', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->string('suffix')->nullable();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('home_hero_panel_pills', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_hero_panel_pills');
        Schema::dropIfExists('home_hero_panel_stats');
        Schema::dropIfExists('home_hero_panel_settings');
    }
};
