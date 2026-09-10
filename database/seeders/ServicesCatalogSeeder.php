<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\Concerns\SeedsReferenceImages;
use Illuminate\Database\Seeder;

class ServicesCatalogSeeder extends Seeder
{
    use SeedsReferenceImages;

    public function run(): void
    {
        $categories = ServiceCategory::query()->get()->keyBy('slug');

        $services = [
            [
                'title' => 'Automatización de procesos',
                'slug' => 'automatizacion-de-procesos',
                'category' => 'automatizacion',
                'description' => 'Optimizamos procesos y eliminamos tareas repetitivas en ventas, operación, cobranzas y administración.',
                'icon' => 'Workflow',
                'image' => 'automation',
                'price_label' => 'Desde $2,800',
                'features' => [
                    'Mapeo del proceso actual',
                    'Flujos automáticos (leads, pedidos, citas)',
                    'Alertas y recordatorios',
                    'Medición de tiempo ahorrado',
                ],
            ],
            [
                'title' => 'Inteligencia artificial',
                'slug' => 'inteligencia-artificial',
                'category' => 'inteligencia-artificial',
                'description' => 'Implementamos IA para clasificar solicitudes, resumir información y acelerar decisiones del día a día.',
                'icon' => 'Lightbulb',
                'image' => 'blog-ai',
                'price_label' => 'Desde $3,500',
                'features' => [
                    'Agentes para consultas internas',
                    'Clasificación de mensajes y tickets',
                    'Resúmenes de reuniones y documentos',
                    'Criterios de calidad con revisión humana',
                ],
            ],
            [
                'title' => 'Software a medida',
                'slug' => 'software-a-medida',
                'category' => 'desarrollo',
                'description' => 'Desarrollamos sistemas adaptados a las necesidades de tu empresa: no fuerzas tu operación a un software genérico.',
                'icon' => 'Code2',
                'image' => 'service-web',
                'price_label' => 'Desde $5,500',
                'features' => [
                    'Web y paneles de operación',
                    'Roles, permisos y auditoría',
                    'Integración con lo que ya usas',
                    'Capacitación al equipo',
                ],
            ],
            [
                'title' => 'Dashboards inteligentes',
                'slug' => 'dashboards-inteligentes',
                'category' => 'datos',
                'description' => 'Visualiza tu negocio en tiempo real: ventas, citas, stock, cobranza y productividad en un solo panel.',
                'icon' => 'Activity',
                'image' => 'analytics',
                'price_label' => 'Desde $2,400',
                'features' => [
                    'Indicadores de gerencia',
                    'Actualización automática',
                    'Alertas cuando un número se sale de rango',
                    'Acceso por celular',
                ],
            ],
            [
                'title' => 'Integraciones',
                'slug' => 'integraciones',
                'category' => 'automatizacion',
                'description' => 'Conectamos tus sistemas y herramientas para que todo funcione como debe ser, sin copiar datos de un lado a otro.',
                'icon' => 'Globe',
                'image' => 'api-architecture',
                'price_label' => 'Desde $1,800',
                'features' => [
                    'WhatsApp, correo y CRM',
                    'Pasarelas de pago y facturación',
                    'Hojas de cálculo y ERP',
                    'Sincronización bidireccional',
                ],
            ],
            [
                'title' => 'Chatbots y agentes inteligentes',
                'slug' => 'chatbots-y-agentes-inteligentes',
                'category' => 'atencion',
                'description' => 'Atención automática, respuestas al instante y mejor experiencia para tus clientes en web, Instagram y WhatsApp.',
                'icon' => 'Headphones',
                'image' => 'service-support',
                'price_label' => 'Desde $2,200',
                'features' => [
                    'WhatsApp Business y web',
                    'Preguntas frecuentes y derivación a humano',
                    'Calificación de leads',
                    'Horario 24/7',
                ],
            ],
            [
                'title' => 'Consultoría de procesos',
                'slug' => 'consultoria-de-procesos',
                'category' => 'automatizacion',
                'description' => 'Antes de programar, entendemos el negocio: qué automatizar primero y qué conviene dejar en manos del equipo.',
                'icon' => 'Search',
                'image' => 'consulting',
                'price_label' => 'Desde $1,500',
                'features' => [
                    'Taller con tu equipo',
                    'Mapa de procesos y dolores',
                    'Priorización por impacto',
                    'Hoja de ruta 90 días',
                ],
            ],
            [
                'title' => 'Acompañamiento continuo',
                'slug' => 'acompanamiento-continuo',
                'category' => 'atencion',
                'description' => 'Mejoramos los flujos mes a mes: nuevos reportes, ajustes de IA y soporte para que la automatización no se quede a medias.',
                'icon' => 'Headset',
                'image' => 'team-dev',
                'price_label' => 'Desde $890/mes',
                'features' => [
                    'Revisión mensual de indicadores',
                    'Ajustes de flujos y prompts',
                    'Soporte en horario laboral',
                    'Backlog de mejoras',
                ],
            ],
        ];

        foreach ($services as $index => $data) {
            $category = $categories->get($data['category']);

            Service::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'service_category_id' => $category?->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'icon' => $data['icon'],
                    'main_image' => $this->referenceImage($data['image'], 960, 540),
                    'price_label' => $data['price_label'],
                    'features' => $data['features'],
                    'sort_order' => $index + 1,
                    'status' => 'published',
                ],
            );
        }

        $validSlugs = collect($services)->pluck('slug')->all();
        Service::query()
            ->whereNotIn('slug', $validSlugs)
            ->update(['status' => 'draft']);
    }
}
