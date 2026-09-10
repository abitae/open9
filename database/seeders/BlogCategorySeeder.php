<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Database\Seeders\Concerns\SeedsReferenceImages;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    use SeedsReferenceImages;

    public function run(): void
    {
        $categories = [
            ['name' => 'Automatización', 'slug' => 'automatizacion', 'description' => 'Procesos, flujos y casos reales de empresas que dejaron de trabajar a mano.', 'image' => 'automation'],
            ['name' => 'Inteligencia artificial', 'slug' => 'ia', 'description' => 'Agentes, chatbots y usos prácticos de IA en negocios peruanos.', 'image' => 'blog-ai'],
            ['name' => 'Operación', 'slug' => 'operacion', 'description' => 'Dashboards, integraciones y el día a día de inmobiliarias, clínicas, restaurantes y comercios.', 'image' => 'dashboard-kpi'],
            ['name' => 'Desarrollo', 'slug' => 'desarrollo', 'description' => 'Software a medida, APIs y buenas prácticas de producto.', 'image' => 'coding-laptop'],
            ['name' => 'Cloud', 'slug' => 'cloud', 'description' => 'AWS, Azure, Google Cloud y arquitectura híbrida.', 'image' => 'cloud-abstract'],
            ['name' => 'Seguridad', 'slug' => 'seguridad', 'description' => 'Ciberseguridad, cumplimiento y protección de datos.', 'image' => 'cybersecurity'],
        ];

        foreach ($categories as $index => $data) {
            $image = $data['image'];
            unset($data['image']);

            BlogCategory::query()->updateOrCreate(
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
