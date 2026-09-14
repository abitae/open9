<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Automatización', 'slug' => 'automatizacion', 'description' => 'Procesos, flujos y eliminación de tareas repetitivas.'],
            ['name' => 'Inteligencia artificial', 'slug' => 'inteligencia-artificial', 'description' => 'Agentes, modelos y decisiones asistidas por IA.'],
            ['name' => 'Software a medida', 'slug' => 'desarrollo', 'description' => 'Sistemas web, mobile e integraciones pensados para tu operación.'],
            ['name' => 'Datos y dashboards', 'slug' => 'datos', 'description' => 'Indicadores en tiempo real para gerencia y operación.'],
            ['name' => 'Atención al cliente', 'slug' => 'atencion', 'description' => 'Chatbots, WhatsApp y agentes de respuesta inmediata.'],
        ];

        foreach ($categories as $index => $data) {
            ServiceCategory::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['sort_order' => $index + 1, 'status' => 'active'],
            );
        }
    }
}
