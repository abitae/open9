<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'site', 'key' => 'name', 'value' => 'OPEN9', 'type' => 'string', 'is_public' => true],
            ['group' => 'site', 'key' => 'tagline', 'value' => 'Expertos en automatización e inteligencia artificial', 'type' => 'string', 'is_public' => true],
            ['group' => 'contact', 'key' => 'email', 'value' => 'empresario.ia@open9.dev', 'type' => 'string', 'is_public' => true],
            ['group' => 'contact', 'key' => 'phone', 'value' => '+51 999 000 000', 'type' => 'string', 'is_public' => true],
            ['group' => 'seo', 'key' => 'description', 'value' => 'OPEN9 transforma procesos manuales en soluciones inteligentes: automatización, IA, software a medida, dashboards, integraciones y chatbots.', 'type' => 'text', 'is_public' => true],
            ['group' => 'social', 'key' => 'links', 'value' => json_encode([]), 'type' => 'json', 'is_public' => true],
            ['group' => 'store', 'key' => 'usd_pen_rate', 'value' => '3.75', 'type' => 'number', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}
