<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use Database\Seeders\Concerns\SeedsReferenceImages;
use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{
    use SeedsReferenceImages;

    public function run(): void
    {
        $categories = [
            ['name' => 'Automatización', 'slug' => 'automatizacion', 'description' => 'Flujos, WhatsApp, RPA e integraciones que eliminan trabajo repetitivo.', 'image' => 'automation'],
            ['name' => 'Software a medida', 'slug' => 'software', 'description' => 'Sistemas, agendas, CRMs y paneles pensados para tu operación.', 'image' => 'service-web'],
            ['name' => 'Comercio y pedidos', 'slug' => 'ecommerce', 'description' => 'Catálogos, delivery, inventario y conciliación de ventas.', 'image' => 'ecommerce'],
            ['name' => 'Datos y dashboards', 'slug' => 'cloud-devops', 'description' => 'Indicadores en tiempo real para gerencia y operación.', 'image' => 'dashboard-kpi'],
            ['name' => 'Infraestructura', 'slug' => 'infraestructura', 'description' => 'Servidores, redes y continuidad cuando el negocio lo requiere.', 'image' => 'datacenter'],
        ];

        foreach ($categories as $index => $data) {
            $image = $data['image'];
            unset($data['image']);

            ProjectCategory::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data + [
                    'image' => $this->referenceImage($image, 800, 450),
                    'sort_order' => $index + 1,
                    'status' => 'active',
                ],
            );
        }
    }
}
