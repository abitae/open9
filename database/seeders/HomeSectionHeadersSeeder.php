<?php

namespace Database\Seeders;

use App\Models\HomeSectionSetting;
use Illuminate\Database\Seeder;

class HomeSectionHeadersSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'section_key' => 'stats',
                'label' => 'Resultados',
                'title' => 'Tecnología que impulsa tu crecimiento',
                'description' => 'Ahorra tiempo, reduce costos y aumenta resultados con procesos inteligentes.',
                'sort_order' => 1,
            ],
            [
                'section_key' => 'platform_services',
                'label' => 'Qué hacemos',
                'title' => 'Soluciones para',
                'title_highlight' => 'automatizar tu empresa',
                'description' => 'Seis capacidades para pasar de lo manual a lo inteligente, sin complicar tu operación.',
                'sort_order' => 2,
            ],
            [
                'section_key' => 'platform_solutions',
                'label' => 'Industrias',
                'title' => 'Soluciones para',
                'title_highlight' => 'tu rubro',
                'description' => 'Adaptamos la automatización y la IA a inmobiliarias, restaurantes, clínicas, comercios, estudios contables y más empresas.',
                'sort_order' => 3,
            ],
            [
                'section_key' => 'services_catalog',
                'label' => 'Servicios',
                'title' => 'De la idea a un',
                'title_highlight' => 'proceso que trabaja solo',
                'description' => 'Automatización, IA, software a medida, dashboards, integraciones y agentes inteligentes.',
                'sort_order' => 4,
            ],
            [
                'section_key' => 'workflow',
                'label' => 'Cómo trabajamos',
                'title' => 'De lo manual a lo',
                'title_highlight' => 'inteligente',
                'description' => 'Diagnóstico, diseño y puesta en marcha. Sin jerga innecesaria y con un plan que se puede medir.',
                'cta_label' => 'Hablar con OPEN9',
                'cta_url' => '/contacto',
                'sort_order' => 5,
            ],
            [
                'section_key' => 'projects_preview',
                'label' => 'Casos reales',
                'title' => 'Empresas que ya',
                'title_highlight' => 'automatizaron',
                'description' => 'Ejemplos reales en español: leads, citas, pedidos, inventario y reportes que antes se hacían a mano.',
                'sort_order' => 6,
            ],
            [
                'section_key' => 'quick_links',
                'label' => 'Explorar',
                'title' => 'Llevamos tu empresa al',
                'title_highlight' => 'siguiente nivel',
                'description' => 'Revisa casos, servicios o escríbenos directo.',
                'sort_order' => 7,
            ],
            [
                'section_key' => 'testimonials',
                'label' => 'Confianza',
                'title' => 'Empresarios que confían en',
                'title_highlight' => 'OPEN9',
                'description' => 'Dueños y gerentes peruanos que automatizaron leads, citas, pedidos y reportes con OPEN9.',
                'sort_order' => 8,
            ],
            [
                'section_key' => 'pricing',
                'label' => 'Planes',
                'title' => 'Empieza por el proceso que más',
                'title_highlight' => 'te cuesta tiempo',
                'description' => 'Desde un flujo puntual hasta una operación completa con agentes y software propio.',
                'sort_order' => 9,
            ],
            [
                'section_key' => 'cta_contact',
                'label' => 'Hablemos',
                'title' => 'Llevamos tu empresa',
                'title_highlight' => 'al siguiente nivel',
                'description' => 'Cuéntanos qué proceso quieres dejar de hacer a mano. Te respondemos en menos de 24 horas a empresario.ia@open9.dev.',
                'sort_order' => 10,
            ],
        ];

        foreach ($sections as $section) {
            HomeSectionSetting::query()->updateOrCreate(
                ['section_key' => $section['section_key']],
                $section + ['is_visible' => true],
            );
        }
    }
}
