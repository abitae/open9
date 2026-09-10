<?php

namespace Database\Seeders;

use App\Models\AiChatSetting;
use App\Models\FooterLink;
use App\Models\FooterLinkGroup;
use App\Models\HomeFeatureCard;
use App\Models\HomeHeroPanelPill;
use App\Models\HomeHeroPanelSetting;
use App\Models\HomeHeroPanelStat;
use App\Models\HomeHeroShowcaseCard;
use App\Models\HomeHeroShowcaseSetting;
use App\Models\HomePricingPlan;
use App\Models\HomeQuickLink;
use App\Models\HomeStat;
use App\Models\HomeWorkflowStep;
use App\Models\LegalPage;
use App\Models\SiteBranding;
use App\Models\SocialLink;
use App\Models\StorageSetting;
use Database\Seeders\Concerns\SeedsReferenceImages;
use Illuminate\Database\Seeder;

class SiteCmsSeeder extends Seeder
{
    use SeedsReferenceImages;

    public function run(): void
    {
        StorageSetting::query()->firstOrCreate(['id' => 1], ['driver' => 'local']);

        SiteBranding::query()->updateOrCreate(['id' => 1], [
            'site_name' => '',
            'tagline' => 'Expertos en automatización e inteligencia artificial',
            'logo_path' => '/logo_normal.png',
            'logo_dark_path' => '/logo_black.png',
            'favicon_path' => '/favicon.png',
            'hero_title' => 'Expertos en automatización e IA',
            'hero_subtitle' => 'Transformamos procesos manuales en soluciones inteligentes: ahorras tiempo, reduces costos y aumentas resultados.',
            'hero_cta_primary_label' => 'Hablemos de tu empresa',
            'hero_cta_primary_url' => '/contacto',
            'hero_cta_secondary_label' => 'Ver casos reales',
            'hero_cta_secondary_url' => '/proyectos',
            'background_video_url' => 'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260315_073750_51473149-4350-4920-ae24-c8214286f323.mp4',
            'contact_email' => 'empresario.ia@open9.dev',
            'contact_phone' => '+51 999 000 009',
            'contact_address' => 'Lima, Perú · Atención remota en todo el país',
            'website_url' => 'https://www.open9.dev',
            'footer_description' => 'OPEN9 diseña automatización, inteligencia artificial y software a medida para inmobiliarias, restaurantes, clínicas, comercios y estudios contables. Menos trabajo repetitivo, más resultados.',
            'copyright_text' => '© 2026 OPEN9 · www.open9.dev · Todos los derechos reservados.',
            'seo_description' => 'OPEN9 — expertos en automatización e IA. Transformamos procesos manuales en soluciones inteligentes que ahorran tiempo, reducen costos y aumentan resultados.',
        ]);

        AiChatSetting::query()->updateOrCreate(['id' => 1], [
            'is_enabled' => true,
            'fab_label' => 'Asistente IA',
            'welcome_message' => 'Hola, soy el asistente de OPEN9. Pregúntame cómo automatizar tu inmobiliaria, restaurante, clínica, comercio o estudio contable.',
            'system_prompt' => 'Eres el asistente virtual de OPEN9, empresa peruana experta en automatización, inteligencia artificial, software a medida, dashboards, integraciones y chatbots. Responde siempre en español, de forma clara y profesional. Ayuda a empresarios a ahorrar tiempo, reducir costos y aumentar resultados. No prometas plazos ni precios exactos: invita a escribir a empresario.ia@open9.dev o a completar el formulario de contacto.',
            'model' => 'gemini-2.0-flash',
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ]);

        $this->seedStats();
        $this->seedHeroPanel();
        $this->seedHeroShowcaseSettings();
        $this->seedHeroShowcase();
        $this->seedFeatureCards();
        $this->seedWorkflow();
        $this->seedQuickLinks();
        $this->seedPricing();
        $this->seedFooter();
        $this->seedLegalPages();

        $this->call(HomeSectionHeadersSeeder::class);
    }

    private function seedStats(): void
    {
        $stats = [
            ['value' => '40', 'suffix' => '%', 'title' => 'Menos tiempo en tareas repetitivas', 'icon' => 'Activity', 'sort_order' => 1],
            ['value' => '25', 'suffix' => '%', 'title' => 'Reducción de costos operativos', 'icon' => 'Award', 'sort_order' => 2],
            ['value' => '24', 'suffix' => '/7', 'title' => 'Atención automática a clientes', 'icon' => 'Headphones', 'sort_order' => 3],
            ['value' => '80', 'suffix' => '+', 'title' => 'Procesos automatizados', 'icon' => 'Rocket', 'sort_order' => 4],
        ];

        foreach ($stats as $stat) {
            HomeStat::query()->updateOrCreate(
                ['sort_order' => $stat['sort_order']],
                $stat + ['status' => 'active', 'is_visible' => true],
            );
        }
    }

    private function seedHeroPanel(): void
    {
        HomeHeroPanelSetting::query()->updateOrCreate(['id' => 1], [
            'badge_label' => 'Expertos en automatización · open9.dev',
            'headline_pre' => 'Expertos en',
            'headline_highlight' => 'automatización',
            'headline_subtitle' => 'Transformamos procesos manuales en',
            'headline_subtitle_highlight' => 'soluciones inteligentes',
            'show_site_name_chip' => true,
            'description' => 'Diseñamos automatizaciones, inteligencia artificial y software a medida que impulsan tu negocio: ahorras tiempo, reduces costos y aumentas resultados.',
            'cta_label' => 'Llevar mi empresa al siguiente nivel',
            'cta_url' => '/contacto',
            'cta_icon' => 'Rocket',
            'quote_kicker' => 'Automatización · IA · Software a medida',
            'quote_primary' => 'Tecnología que impulsa',
            'quote_secondary' => 'tu crecimiento.',
            'quote_footer' => 'www.open9.dev',
            'media_type' => 'image',
            'image_path' => $this->referenceImage('team-meeting', 960, 720),
        ]);

        $heroPanelStats = [
            ['value' => '40', 'suffix' => '%', 'label' => 'Menos tiempo', 'sort_order' => 1],
            ['value' => '25', 'suffix' => '%', 'label' => 'Menos costos', 'sort_order' => 2],
            ['value' => '24', 'suffix' => '/7', 'label' => 'Atención IA', 'sort_order' => 3],
        ];
        foreach ($heroPanelStats as $stat) {
            HomeHeroPanelStat::query()->updateOrCreate(
                ['sort_order' => $stat['sort_order']],
                $stat + ['status' => 'active', 'is_visible' => true],
            );
        }

        $heroPills = [
            ['label' => 'Automatización', 'sort_order' => 1],
            ['label' => 'Inteligencia artificial', 'sort_order' => 2],
            ['label' => 'Software a medida', 'sort_order' => 3],
        ];
        foreach ($heroPills as $pill) {
            HomeHeroPanelPill::query()->updateOrCreate(
                ['sort_order' => $pill['sort_order']],
                $pill + ['status' => 'active', 'is_visible' => true],
            );
        }
    }

    private function seedHeroShowcaseSettings(): void
    {
        HomeHeroShowcaseSetting::query()->updateOrCreate(['id' => 1], [
            'badge_label' => 'Casos reales · Automatización · IA',
        ]);
    }

    private function seedHeroShowcase(): void
    {
        $heroCards = [
            [
                'title' => 'Automatización de procesos',
                'layout' => 'compact',
                'description' => 'Eliminamos tareas repetitivas en ventas, operación y administración para que tu equipo se enfoque en crecer.',
                'icon' => 'Workflow',
                'media_type' => 'image',
                'image_path' => $this->referenceImage('chatbot-whatsapp', 600, 400),
                'sort_order' => 1,
            ],
            [
                'title' => 'Inteligencia artificial aplicada',
                'layout' => 'compact',
                'description' => 'Agentes, clasificadores y asistentes que responden al instante y te ayudan a tomar mejores decisiones.',
                'icon' => 'Lightbulb',
                'media_type' => 'image',
                'image_path' => $this->referenceImage('blog-ai', 600, 400),
                'sort_order' => 2,
            ],
            [
                'title' => 'Software y dashboards a medida',
                'layout' => 'featured',
                'description' => 'Sistemas, paneles e integraciones pensados para inmobiliarias, restaurantes, clínicas, comercios y estudios contables.',
                'icon' => 'Code2',
                'media_type' => 'image',
                'image_path' => $this->referenceImage('dashboard-kpi', 800, 500),
                'sort_order' => 3,
            ],
        ];

        foreach ($heroCards as $card) {
            HomeHeroShowcaseCard::query()->updateOrCreate(
                ['sort_order' => $card['sort_order']],
                $card + ['status' => 'active', 'is_visible' => true],
            );
        }
    }

    private function seedFeatureCards(): void
    {
        $cards = [
            ['card_type' => 'service', 'title' => 'Automatización', 'description' => 'Optimizamos procesos y eliminamos tareas repetitivas en ventas, operación y backoffice.', 'icon' => 'Workflow', 'sort_order' => 1],
            ['card_type' => 'service', 'title' => 'Inteligencia artificial', 'description' => 'Implementamos IA para tomar mejores decisiones, acelerar resultados y atender sin horarios.', 'icon' => 'Lightbulb', 'sort_order' => 2],
            ['card_type' => 'service', 'title' => 'Software a medida', 'description' => 'Desarrollamos sistemas adaptados a las necesidades reales de tu empresa, no plantillas genéricas.', 'icon' => 'Code2', 'sort_order' => 3],
            ['card_type' => 'service', 'title' => 'Dashboards inteligentes', 'description' => 'Visualiza tu negocio en tiempo real y toma decisiones con datos, no con hojas de cálculo sueltas.', 'icon' => 'Activity', 'sort_order' => 4],
            ['card_type' => 'service', 'title' => 'Integraciones', 'description' => 'Conectamos tus sistemas y herramientas para que todo funcione como debe ser, sin copiar y pegar.', 'icon' => 'Globe', 'sort_order' => 5],
            ['card_type' => 'service', 'title' => 'Chatbots y agentes inteligentes', 'description' => 'Atención automática, respuestas al instante y mejor experiencia para tus clientes en web y WhatsApp.', 'icon' => 'Headphones', 'sort_order' => 6],
            ['card_type' => 'solution', 'client_type' => 'inmobiliarias', 'title' => 'Inmobiliarias', 'description' => 'Captura de leads, seguimiento de visitas y respuestas automáticas a consultas de propiedades.', 'icon' => 'FolderKanban', 'sort_order' => 7],
            ['card_type' => 'solution', 'client_type' => 'restaurantes', 'title' => 'Restaurantes', 'description' => 'Pedidos, reservas, inventario de cocina y reportes diarios sin planillas a medianoche.', 'icon' => 'ClipboardList', 'sort_order' => 8],
            ['card_type' => 'solution', 'client_type' => 'clinicas', 'title' => 'Clínicas', 'description' => 'Agenda, recordatorios, historial y confirmación de citas para reducir inasistencias.', 'icon' => 'Shield', 'sort_order' => 9],
            ['card_type' => 'solution', 'client_type' => 'comercios', 'title' => 'Comercios', 'description' => 'Catálogo, stock, pedidos y conciliación de ventas en un solo flujo.', 'icon' => 'ShoppingBag', 'sort_order' => 10],
            ['card_type' => 'solution', 'client_type' => 'estudios-contables', 'title' => 'Estudios contables', 'description' => 'Recolección de documentos, conciliación y tableros de cumplimiento para tus clientes.', 'icon' => 'ClipboardList', 'sort_order' => 11],
            ['card_type' => 'solution', 'client_type' => 'empresas', 'title' => 'Y más empresas', 'description' => 'Si tu operación tiene procesos repetitivos, podemos automatizarlos y medir el impacto.', 'icon' => 'Rocket', 'sort_order' => 12],
        ];

        $titles = [];
        foreach ($cards as $card) {
            $titles[] = $card['title'];
            HomeFeatureCard::query()->updateOrCreate(
                ['title' => $card['title'], 'card_type' => $card['card_type']],
                $card + ['status' => 'active', 'is_visible' => true],
            );
        }

        HomeFeatureCard::query()
            ->whereNotIn('title', $titles)
            ->update(['is_visible' => false, 'status' => 'inactive']);
    }

    private function seedWorkflow(): void
    {
        $steps = [
            ['step_number' => 1, 'title' => 'Diagnóstico', 'description' => 'Mapeamos tus procesos actuales: qué se hace a mano, dónde se pierde tiempo y qué duele de verdad.', 'icon' => 'Search', 'sort_order' => 1],
            ['step_number' => 2, 'title' => 'Diseño', 'description' => 'Proponemos automatizaciones, agentes y paneles con un plan claro de implementación y retorno.', 'icon' => 'PenTool', 'sort_order' => 2],
            ['step_number' => 3, 'title' => 'Puesta en marcha', 'description' => 'Implementamos, integramos tus herramientas y dejamos al equipo operando con soporte cercano.', 'icon' => 'Rocket', 'sort_order' => 3],
        ];

        foreach ($steps as $step) {
            HomeWorkflowStep::query()->updateOrCreate(
                ['step_number' => $step['step_number']],
                $step + ['status' => 'active', 'is_visible' => true],
            );
        }
    }

    private function seedQuickLinks(): void
    {
        $links = [
            ['title' => 'Casos reales', 'description' => 'Automatizaciones en inmobiliarias, clínicas, restaurantes y más.', 'link_url' => '/proyectos', 'icon' => 'FolderKanban', 'sort_order' => 1],
            ['title' => 'Servicios', 'description' => 'Automatización, IA, software, dashboards, integraciones y chatbots.', 'link_url' => '/servicios', 'icon' => 'Workflow', 'sort_order' => 2],
            ['title' => 'Hablemos', 'description' => 'Cuéntanos tu proceso. Te respondemos en menos de 24 horas.', 'link_url' => '/contacto', 'icon' => 'Headphones', 'sort_order' => 3],
        ];

        foreach ($links as $link) {
            HomeQuickLink::query()->updateOrCreate(
                ['link_url' => $link['link_url']],
                $link + ['status' => 'active', 'is_visible' => true],
            );
        }

        HomeQuickLink::query()
            ->whereNotIn('link_url', collect($links)->pluck('link_url'))
            ->update(['is_visible' => false, 'status' => 'inactive']);
    }

    private function seedPricing(): void
    {
        $plans = [
            [
                'name' => 'Impulso',
                'price' => 'Desde $2,800',
                'period' => 'proyecto',
                'description' => 'Ideal para automatizar un proceso crítico: leads, citas o reportes.',
                'features' => ['Diagnóstico de un flujo clave', 'Automatización o chatbot inicial', 'Dashboard simple de seguimiento', 'Soporte 30 días'],
                'cta_text' => 'Empezar',
                'cta_url' => '/contacto',
                'is_highlighted' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Crecimiento',
                'price' => 'Desde $6,500',
                'period' => 'proyecto',
                'description' => 'Para empresas que quieren IA, integraciones y un panel de operación.',
                'features' => ['Todo Impulso', 'Agente o chatbot con IA', 'Integración de 2-3 sistemas', 'Dashboard en tiempo real', 'Soporte 90 días'],
                'cta_text' => 'Solicitar propuesta',
                'cta_url' => '/contacto',
                'is_highlighted' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Empresa',
                'price' => 'A medida',
                'period' => '',
                'description' => 'Operación completa: varios procesos, agentes y software propio.',
                'features' => ['Equipo dedicado OPEN9', 'Varios flujos automatizados', 'Software a medida', 'Acompañamiento continuo'],
                'cta_text' => 'Agendar conversación',
                'cta_url' => '/contacto',
                'is_highlighted' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            HomePricingPlan::query()->updateOrCreate(
                ['name' => $plan['name']],
                $plan + ['status' => 'active', 'is_visible' => true],
            );
        }

        HomePricingPlan::query()
            ->whereNotIn('name', collect($plans)->pluck('name'))
            ->update(['is_visible' => false, 'status' => 'inactive']);
    }

    private function seedFooter(): void
    {
        $groups = [
            [
                'title' => 'Navegación',
                'sort_order' => 1,
                'links' => [
                    ['Inicio', '/', false],
                    ['Servicios', '/servicios', false],
                    ['Proyectos', '/proyectos', false],
                    ['Blog', '/blog', false],
                    ['Tienda', '/tienda', false],
                    ['Contacto', '/contacto', false],
                ],
            ],
            [
                'title' => 'Soluciones',
                'sort_order' => 2,
                'links' => [
                    ['Automatización', '/servicios', false],
                    ['Chatbots e IA', '/servicios', false],
                    ['Dashboards', '/proyectos', false],
                    ['Software a medida', '/contacto', false],
                    ['Integraciones', '/servicios', false],
                ],
            ],
            [
                'title' => 'Empresa',
                'sort_order' => 3,
                'links' => [
                    ['Casos reales', '/proyectos', false],
                    ['Empresarios que confían', '/#testimonios', false],
                    ['Hablemos', '/contacto', false],
                    ['Mi cuenta', '/ingresar', false],
                    ['empresario.ia@open9.dev', 'mailto:empresario.ia@open9.dev', true],
                ],
            ],
            [
                'title' => 'Legal',
                'sort_order' => 4,
                'links' => [
                    ['Privacidad', '/legal/privacidad', false],
                    ['Términos', '/legal/terminos', false],
                    ['Cookies', '/legal/cookies', false],
                ],
            ],
        ];

        $keepTitles = [];
        foreach ($groups as $groupData) {
            $keepTitles[] = $groupData['title'];
            $group = FooterLinkGroup::query()->updateOrCreate(
                ['title' => $groupData['title']],
                ['sort_order' => $groupData['sort_order'], 'is_visible' => true],
            );

            $keepLabels = [];
            foreach ($groupData['links'] as $index => [$label, $url, $external]) {
                $keepLabels[] = $label;
                FooterLink::query()->updateOrCreate(
                    ['footer_link_group_id' => $group->id, 'label' => $label],
                    ['url' => $url, 'sort_order' => $index + 1, 'is_external' => $external],
                );
            }

            FooterLink::query()
                ->where('footer_link_group_id', $group->id)
                ->whereNotIn('label', $keepLabels)
                ->delete();
        }

        FooterLinkGroup::query()
            ->whereNotIn('title', $keepTitles)
            ->update(['is_visible' => false]);

        $social = [
            ['platform' => 'linkedin', 'url' => 'https://www.linkedin.com/company/open9', 'sort_order' => 1],
            ['platform' => 'instagram', 'url' => 'https://www.instagram.com/open9.dev', 'sort_order' => 2],
            ['platform' => 'youtube', 'url' => 'https://www.youtube.com/@open9dev', 'sort_order' => 3],
            ['platform' => 'facebook', 'url' => 'https://www.facebook.com/open9.dev', 'sort_order' => 4],
        ];

        foreach ($social as $link) {
            SocialLink::query()->updateOrCreate(
                ['platform' => $link['platform']],
                $link + ['is_visible' => true],
            );
        }

        SocialLink::query()
            ->whereNotIn('platform', collect($social)->pluck('platform'))
            ->update(['is_visible' => false]);
    }

    private function seedLegalPages(): void
    {
        $pages = [
            'privacidad' => [
                'title' => 'Política de Privacidad',
                'intro' => 'En OPEN9 protegemos los datos de clientes, prospectos y usuarios de nuestros sistemas de automatización e inteligencia artificial.',
            ],
            'terminos' => [
                'title' => 'Términos y Condiciones',
                'intro' => 'Estos términos regulan el uso del sitio open9.dev y la contratación de servicios de automatización, software e inteligencia artificial.',
            ],
            'cookies' => [
                'title' => 'Política de Cookies',
                'intro' => 'Usamos cookies técnicas y de medición para operar el sitio, recordar preferencias y mejorar la experiencia de navegación.',
            ],
        ];

        foreach ($pages as $slug => $page) {
            LegalPage::query()->updateOrCreate(['slug' => $slug], [
                'title' => $page['title'],
                'status' => 'published',
                'published_at' => now(),
                'blocks' => [
                    ['type' => 'heading', 'content' => $page['title']],
                    ['type' => 'paragraph', 'content' => $page['intro']],
                    ['type' => 'paragraph', 'content' => 'Para ejercer derechos ARCO o consultas sobre tratamiento de datos, escríbenos a empresario.ia@open9.dev.'],
                ],
            ]);
        }
    }
}
