<?php

namespace Database\Seeders\Concerns;

trait SeedsReferenceImages
{
    /**
     * Fotografías reales de Unsplash curadas por tema.
     *
     * @var array<string, string>
     */
    private const PHOTO_CATALOG = [
        // Infraestructura y hardware
        'datacenter' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31',
        'server-rack' => 'https://images.unsplash.com/photo-1544197150-0f43e44182c8',
        'network-switch' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e',
        'nas-storage' => 'https://images.unsplash.com/photo-1597872200969-2b65d56ff4c1',
        'workstation' => 'https://images.unsplash.com/photo-1593640408182-31c70c8268f5',
        'ups-power' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e',
        'cables-rack' => 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13',

        // Cloud y DevOps
        'cloud-abstract' => 'https://images.unsplash.com/photo-1451187580455-43490279c0fa',
        'cloud-dashboard' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71',
        'kubernetes' => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9',
        'devops-pipeline' => 'https://images.unsplash.com/photo-1618477388954-7852f034d369',
        'aws-region' => 'https://images.unsplash.com/photo-1451187580455-43490279c0fa',

        // Desarrollo de software
        'coding-laptop' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085',
        'team-dev' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c',
        'react-code' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee',
        'mobile-app' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c',
        'api-architecture' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c',

        // Seguridad y consultoría
        'cybersecurity' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3',
        'consulting' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf',
        'handshake' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216',

        // Proyectos / casos de uso
        'erp-dashboard' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f',
        'ecommerce' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d',
        'analytics' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71',
        'automation' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e',
        'logistics' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d',
        'warehouse' => 'https://images.unsplash.com/photo-1553413077-190dd305871c',
        'fintech' => 'https://images.unsplash.com/photo-1563986768494-4dffdc115039',
        'healthcare-tech' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d',
        'clinic-desk' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d',
        'real-estate' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa',
        'restaurant' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0',
        'restaurant-service' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4',
        'kitchen' => 'https://images.unsplash.com/photo-1556910103-1c0279aa3b47',
        'accounting' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f',
        'documents' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85',
        'hotel' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945',
        'classroom' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644',
        'retail' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8',
        'chatbot-whatsapp' => 'https://images.unsplash.com/photo-1611746872915-64389b06c455',
        'dashboard-kpi' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f',
        'team-meeting' => 'https://images.unsplash.com/photo-1600880292089-90a7e086ee0c',
        'office-modern' => 'https://images.unsplash.com/photo-1497366216548-37526070297c',

        // Blog
        'blog-laravel' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c',
        'blog-livewire' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6',
        'blog-postgres' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d',
        'blog-payments' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3',
        'blog-architecture' => 'https://images.unsplash.com/photo-1555949963-aa79dcee981c',
        'blog-aws' => 'https://images.unsplash.com/photo-1451187580455-43490279c0fa',
        'blog-react' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee',
        'blog-k8s' => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9',
        'blog-ai' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995',
        'blog-security' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3',
        'blog-microservices' => 'https://images.unsplash.com/photo-1618477388954-7852f034d369',
        'blog-career' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f',
        'blog-monitoring' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71',

        // Productos tienda
        'product-rack' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31',
        'product-cloud-pro' => 'https://images.unsplash.com/photo-1451187580455-43490279c0fa',
        'product-switch' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e',
        'product-nas' => 'https://images.unsplash.com/photo-1597872200969-2b65d56ff4c1',
        'product-devops' => 'https://images.unsplash.com/photo-1618477388954-7852f034d369',
        'product-firewall' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3',
        'product-cables' => 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13',
        'product-ups' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e',
        'product-aws' => 'https://images.unsplash.com/photo-1451187580455-43490279c0fa',
        'product-workstation' => 'https://images.unsplash.com/photo-1593640408182-31c70c8268f5',

        // Servicios
        'service-consulting' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf',
        'service-cloud' => 'https://images.unsplash.com/photo-1451187580455-43490279c0fa',
        'service-web' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085',
        'service-hardware' => 'https://images.unsplash.com/photo-1544197150-0f43e44182c8',
        'service-security' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3',
        'service-mobile' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c',
        'service-devops' => 'https://images.unsplash.com/photo-1618477388954-7852f034d369',
        'service-support' => 'https://images.unsplash.com/photo-1553877522-43269d4ea984',

        // Personas (testimonios y clientes) — retratos reales
        'person-1' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a',
        'person-2' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2',
        'person-3' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e',
        'person-4' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956',
        'person-5' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7',
        'person-6' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d',
        'person-7' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80',
        'person-8' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e',
        'person-9' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2',
        'person-10' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb',
        'person-11' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d',
        'person-12' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6',
    ];

    /**
     * Galerías de fotos reales distintas (evita claves inexistentes).
     *
     * @var array<string, list<string>>
     */
    private const GALLERY_SETS = [
        'real-estate' => ['real-estate', 'office-modern', 'consulting'],
        'restaurant' => ['restaurant', 'kitchen', 'restaurant-service'],
        'healthcare-tech' => ['healthcare-tech', 'clinic-desk', 'team-meeting'],
        'ecommerce' => ['ecommerce', 'retail', 'warehouse'],
        'accounting' => ['accounting', 'documents', 'dashboard-kpi'],
        'blog-ai' => ['blog-ai', 'chatbot-whatsapp', 'coding-laptop'],
        'analytics' => ['analytics', 'dashboard-kpi', 'cloud-dashboard'],
        'restaurant-service' => ['restaurant-service', 'restaurant', 'kitchen'],
        'hotel' => ['hotel', 'office-modern', 'handshake'],
        'logistics' => ['logistics', 'warehouse', 'automation'],
        'fintech' => ['fintech', 'dashboard-kpi', 'cybersecurity'],
        'classroom' => ['classroom', 'blog-career', 'team-dev'],
        'retail' => ['retail', 'ecommerce', 'warehouse'],
        'chatbot-whatsapp' => ['chatbot-whatsapp', 'mobile-app', 'blog-ai'],
        'dashboard-kpi' => ['dashboard-kpi', 'analytics', 'cloud-dashboard'],
        'consulting' => ['consulting', 'handshake', 'team-meeting'],
        'api-architecture' => ['api-architecture', 'coding-laptop', 'devops-pipeline'],
        'product-rack' => ['product-rack', 'datacenter', 'server-rack'],
        'product-nas' => ['product-nas', 'nas-storage', 'datacenter'],
        'product-switch' => ['product-switch', 'network-switch', 'cables-rack'],
        'product-workstation' => ['product-workstation', 'workstation', 'coding-laptop'],
        'product-firewall' => ['product-firewall', 'cybersecurity', 'datacenter'],
        'product-ups' => ['product-ups', 'ups-power', 'server-rack'],
        'product-cables' => ['product-cables', 'cables-rack', 'network-switch'],
        'product-aws' => ['product-aws', 'cloud-abstract', 'cloud-dashboard'],
        'product-cloud-pro' => ['product-cloud-pro', 'cloud-dashboard', 'kubernetes'],
        'product-devops' => ['product-devops', 'devops-pipeline', 'kubernetes'],
        'kubernetes' => ['kubernetes', 'devops-pipeline', 'cloud-abstract'],
        'server-rack' => ['server-rack', 'datacenter', 'product-rack'],
        'automation' => ['automation', 'dashboard-kpi', 'team-dev'],
    ];

    protected function referenceImage(string $key, int $width = 1200, int $height = 675): string
    {
        $base = self::PHOTO_CATALOG[$key] ?? self::PHOTO_CATALOG['coding-laptop'];

        return "{$base}?auto=format&fit=crop&w={$width}&h={$height}&q=80";
    }

    /**
     * @return list<string>
     */
    protected function referenceGallery(string $key, int $count = 3, int $width = 800, int $height = 500): array
    {
        $set = self::GALLERY_SETS[$key] ?? [$key, 'team-dev', 'office-modern'];
        $gallery = [];

        for ($i = 0; $i < $count; $i++) {
            $gallery[] = $this->referenceImage($set[$i % count($set)], $width, $height);
        }

        return $gallery;
    }

    /**
     * @param  list<string>  $paragraphs
     */
    protected function paragraphs(...$paragraphs): string
    {
        return implode("\n\n", $paragraphs);
    }
}
