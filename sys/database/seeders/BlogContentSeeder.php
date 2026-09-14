<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Database\Seeders\Concerns\SeedsReferenceImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BlogContentSeeder extends Seeder
{
    use SeedsReferenceImages;

    public function run(): void
    {
        $editor = User::query()->where('email', 'editor@open9.dev')->first()
            ?? User::query()->where('email', 'admin@open9.dev')->firstOrFail();

        $categories = BlogCategory::query()->get()->keyBy('slug');
        $defaultCategory = $categories->first();

        $this->seedTags();
        $this->seedPosts($editor, $categories, $defaultCategory);
    }

    private function seedTags(): void
    {
        foreach (['Automatización', 'IA', 'WhatsApp', 'Dashboards', 'Laravel', 'Livewire', 'React', 'Integraciones', 'Chatbots', 'Inmobiliarias', 'Clínicas', 'Restaurantes', 'Comercios', 'AWS', 'Azure', 'Docker', 'Kubernetes', 'Seguridad', 'PostgreSQL', 'DevOps', 'Frontend', 'Cloud'] as $name) {
            BlogTag::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }

    /**
     * @param  Collection<string, BlogCategory>  $categories
     */
    private function seedPosts(User $editor, $categories, BlogCategory $defaultCategory): void
    {
        $posts = [
            [
                'title' => 'Cómo dejar de perder leads en WhatsApp (caso inmobiliaria)',
                'category' => 'automatizacion',
                'image' => 'real-estate',
                'tags' => ['Automatización', 'WhatsApp', 'Inmobiliarias'],
                'featured' => true,
                'weeks_ago' => 1,
                'excerpt' => 'Un agente responde en minutos, califica presupuesto y distrito, y deja al asesor solo las visitas que sí van a concretarse.',
                'content' => $this->paragraphs(
                    'En inmobiliarias el fin de semana es cuando más preguntan y cuando menos hay alguien detrás del celular. El lead se enfría.',
                    'El patrón que repetimos en OPEN9 es simple: cada mensaje crea una ficha, un agente responde lo frecuente (precio, metraje, financiamiento) y un humano agenda la visita.',
                    'Mide tres números: tiempo de primera respuesta, porcentaje de visitas confirmadas y citas que no llegan. Si no bajas el primero, el resto no mejora.',
                ),
            ],
            [
                'title' => 'Recordatorios que sí bajan las inasistencias en clínicas',
                'category' => 'ia',
                'image' => 'healthcare-tech',
                'tags' => ['IA', 'Chatbots', 'Clínicas'],
                'featured' => true,
                'weeks_ago' => 2,
                'excerpt' => 'No hace falta un HIS completo. Hace falta confirmar la cita 24 horas y 2 horas antes, y rellenar el hueco con lista de espera.',
                'content' => $this->paragraphs(
                    'Recepción no debería pasar la mañana persiguiendo pacientes. Ese trabajo es repetible y se automatiza bien.',
                    'Un flujo de WhatsApp con dos recordatorios y un botón para reprogramar suele recortar inasistencias a la mitad en el primer trimestre.',
                    'La IA entra después: clasificar el motivo de la cancelación y sugerir el mejor horario de reemplazo. Primero el recordatorio, luego el modelo.',
                ),
            ],
            [
                'title' => 'Pedidos de restaurante sin papel ni Excel a medianoche',
                'category' => 'operacion',
                'image' => 'restaurant',
                'tags' => ['Automatización', 'Restaurantes', 'Dashboards'],
                'featured' => true,
                'weeks_ago' => 3,
                'excerpt' => 'La comanda llega a cocina en pantalla, el delivery tiene estados y el cierre de caja sale solo. El dueño duerme.',
                'content' => $this->paragraphs(
                    'El dolor no es “falta un sistema famoso”. Es que el pedido se anota dos veces y la caja no cuadra.',
                    'Empieza por un canal (WhatsApp o web), una pantalla de cocina y un reporte de medios de pago. Eso ya ahorra horas.',
                    'Cuando eso corre estable, agrega ranking de platos y horarios pico. Ahí la IA puede estimar demanda; no al revés.',
                ),
            ],
            [
                'title' => 'Qué automatizar primero en un comercio pequeño',
                'category' => 'operacion',
                'image' => 'ecommerce',
                'tags' => ['Automatización', 'Comercios', 'Dashboards'],
                'featured' => true,
                'weeks_ago' => 4,
                'excerpt' => 'Stock, no marketing. Si vendes por Instagram y mostrador sin descontar inventario, estás vendiendo humo.',
                'content' => $this->paragraphs(
                    'Muchos dueños piden un chatbot cuando el verdadero hueco es no saber qué hay en anaquel.',
                    'Prioridad: un catálogo único, descuento de stock por cada venta y un tablero de lo que se movió ayer.',
                    'El chatbot suma cuando ya no prometes productos agotados. Automatizar el caos solo acelera el caos.',
                ),
            ],
            [
                'title' => 'Cómo construir dashboards administrativos compactos',
                'category' => 'desarrollo',
                'image' => 'blog-laravel',
                'tags' => ['Laravel', 'Livewire', 'Frontend'],
                'featured' => true,
                'weeks_ago' => 2,
                'excerpt' => 'Patrones de diseño para paneles administrativos densos en información sin sacrificar usabilidad.',
                'content' => $this->paragraphs(
                    'Los dashboards administrativos modernos deben mostrar KPIs, tablas y acciones rápidas en un solo viewport. En OPEN9 usamos Livewire con componentes atómicos y lazy loading para mantener tiempos de respuesta bajo 200 ms.',
                    'Recomendamos agrupar métricas por contexto (ventas, operaciones, soporte) y usar colores semánticos consistentes. Evite más de 6 widgets principales por pantalla.',
                    'La clave está en combinar server-side rendering con actualizaciones parciales: el usuario percibe una SPA sin la complejidad de un frontend separado.',
                ),
            ],
            [
                'title' => 'Buenas prácticas con Livewire y FluxUI',
                'category' => 'desarrollo',
                'image' => 'blog-livewire',
                'tags' => ['Livewire', 'Laravel', 'Frontend'],
                'featured' => true,
                'weeks_ago' => 3,
                'excerpt' => 'Componentes reutilizables, validación en tiempo real y accesibilidad en backoffice Laravel.',
                'content' => $this->paragraphs(
                    'FluxUI ofrece primitivos accesibles que encajan naturalmente con Livewire 3. Separe la lógica de negocio en actions o services y deje los componentes enfocados en presentación.',
                    'Use wire:model.live.debounce para búsquedas y filtros; para formularios largos prefiera wire:model.blur para reducir round-trips.',
                    'En proyectos OPEN9 documentamos cada componente custom en Storybook interno para acelerar la incorporación de nuevos desarrolladores.',
                ),
            ],
            [
                'title' => 'PostgreSQL para plataformas educativas',
                'category' => 'desarrollo',
                'image' => 'blog-postgres',
                'tags' => ['PostgreSQL', 'Laravel', 'Cloud'],
                'featured' => false,
                'weeks_ago' => 4,
                'excerpt' => 'Índices, particionado y réplicas de lectura para LMS con alto volumen de inscripciones.',
                'content' => $this->paragraphs(
                    'Las plataformas educativas concentran picos de carga en fechas de matrícula. PostgreSQL 16 con índices parciales en enrollments(status) reduce consultas de listado un 60%.',
                    'Para reportes históricos implementamos réplicas de lectura y materialized views refrescadas cada hora.',
                    'JSONB es útil para metadatos de cursos variables, pero no reemplace columnas tipadas para campos que filtra frecuentemente.',
                ),
            ],
            [
                'title' => 'Automatización de pagos y certificados',
                'category' => 'desarrollo',
                'image' => 'blog-payments',
                'tags' => ['Laravel', 'Seguridad', 'DevOps'],
                'featured' => false,
                'weeks_ago' => 5,
                'excerpt' => 'Flujos de verificación de vouchers, emisión de certificados PDF y notificaciones automáticas.',
                'content' => $this->paragraphs(
                    'Automatizar pagos manuales (Yape, Plin, transferencia) requiere colas resilientes y estados explícitos: pending, under_review, approved, rejected.',
                    'Los certificados se generan solo tras confirmar enrollment y pago; usamos jobs idempotentes con unique constraints para evitar duplicados.',
                    'Cada certificado incluye código QR de verificación pública y hash SHA-256 del PDF almacenado en S3.',
                ),
            ],
            [
                'title' => 'Arquitectura modular en Laravel',
                'category' => 'desarrollo',
                'image' => 'blog-architecture',
                'tags' => ['Laravel', 'Docker', 'DevOps'],
                'featured' => false,
                'weeks_ago' => 6,
                'excerpt' => 'Bounded contexts, service providers y contratos para escalar equipos sin acoplamiento.',
                'content' => $this->paragraphs(
                    'Organizamos código por dominio (Academia, Pagos, CMS) con namespaces y service providers dedicados. Las dependencias cruzadas pasan por interfaces en app/Contracts.',
                    'Events y listeners desacoplan efectos secundarios: un pago aprobado dispara enrollment confirmado, email y auditoría sin orquestación manual.',
                    'Esta estructura nos permitió extraer el módulo de tienda a un package interno reutilizable en tres clientes.',
                ),
            ],
            [
                'title' => 'Migración a AWS: lecciones de un proyecto real',
                'category' => 'cloud',
                'image' => 'blog-aws',
                'tags' => ['AWS', 'Cloud', 'Docker'],
                'featured' => true,
                'weeks_ago' => 1,
                'excerpt' => 'Assessment, piloto y cutover: cómo migramos 12 servicios on-premise sin downtime crítico.',
                'content' => $this->paragraphs(
                    'La migración de RetailMax comenzó con un mapa de dependencias y clasificación por criticidad. Servicios stateless fueron los primeros en ECS Fargate.',
                    'RDS Multi-AZ y backups automatizados reemplazaron el cluster PostgreSQL on-premise. CloudWatch alarms cubren CPU, conexiones y latencia p99.',
                    'El ahorro de costos del 28% se logró con Reserved Instances y rightsizing tras 60 días de métricas reales.',
                ),
            ],
            [
                'title' => 'React 19 y Vite: stack moderno para el frontend OPEN9',
                'category' => 'desarrollo',
                'image' => 'blog-react',
                'tags' => ['React', 'Frontend'],
                'featured' => true,
                'weeks_ago' => 7,
                'excerpt' => 'Server components no aplican aquí, pero React 19 simplifica formularios y suspense en nuestra SPA.',
                'content' => $this->paragraphs(
                    'El sitio público OPEN9 usa React 19 con Vite 6, TypeScript estricto y Tailwind v4. Fetch de configuración CMS en un provider raíz evita prop drilling.',
                    'Lazy loading de rutas y imágenes con loading="lazy" mantienen LCP bajo 2.5 s en 4G.',
                    'Integramos animaciones con Framer Motion solo en hero y transiciones de página para no penalizar dispositivos modestos.',
                ),
            ],
            [
                'title' => 'Kubernetes en producción: checklist para equipos pequeños',
                'category' => 'operacion',
                'image' => 'blog-k8s',
                'tags' => ['Kubernetes', 'DevOps', 'Azure'],
                'featured' => false,
                'weeks_ago' => 8,
                'excerpt' => 'Lo mínimo viable antes de exponer workloads a tráfico real: recursos, probes y secrets.',
                'content' => $this->paragraphs(
                    'Antes de producción: define requests/limits, liveness/readiness probes, NetworkPolicies y backup de etcd o managed control plane.',
                    'Usa Helm o Kustomize desde el día uno; evita kubectl apply de YAML suelto en más de dos entornos.',
                    'Para equipos de 3-5 personas, EKS o AKS managed reduce la carga operativa frente a clusters self-hosted.',
                ),
            ],
            [
                'title' => 'IA generativa en soporte técnico: casos de uso',
                'category' => 'ia',
                'image' => 'blog-ai',
                'tags' => ['IA', 'Cloud'],
                'featured' => false,
                'weeks_ago' => 9,
                'excerpt' => 'Clasificación de tickets, borradores de respuesta y base de conocimiento asistida por embeddings.',
                'content' => $this->paragraphs(
                    'Implementamos un asistente interno que sugiere respuestas basadas en tickets resueltos y documentación Confluence indexada con embeddings.',
                    'La clasificación automática reduce un 35% el tiempo de triage en mesa L1. Siempre hay revisión humana antes de enviar al cliente.',
                    'Cuidado con datos sensibles: anonimizamos logs y usamos modelos en región con DPA firmado.',
                ),
            ],
            [
                'title' => 'Seguridad en APIs Laravel: autenticación y rate limiting',
                'category' => 'seguridad',
                'image' => 'blog-security',
                'tags' => ['Laravel', 'Seguridad'],
                'featured' => false,
                'weeks_ago' => 10,
                'excerpt' => 'Sanctum, políticas de autorización, throttling y validación estricta en endpoints públicos.',
                'content' => $this->paragraphs(
                    'Toda API pública de OPEN9 usa Sanctum con tokens de corta duración y scopes por recurso. Rate limiting por IP y por token previene abuso.',
                    'Form Requests centralizan validación; nunca confíe solo en validación frontend. Registre intentos fallidos para detección de fuerza bruta.',
                    'Headers de seguridad (CSP, HSTS, X-Frame-Options) se configuran en middleware y se verifican en CI con securityheaders.com.',
                ),
            ],
            [
                'title' => 'De monolito a microservicios sin perder el control',
                'category' => 'operacion',
                'image' => 'blog-microservices',
                'tags' => ['Docker', 'Cloud', 'PostgreSQL'],
                'featured' => false,
                'weeks_ago' => 11,
                'excerpt' => 'Strangler fig pattern: extraer bounded contexts cuando el dolor supera el beneficio del monolito.',
                'content' => $this->paragraphs(
                    'No microservicios por moda. Extraiga primero el dominio con mayor fricción de despliegue o escalado independiente.',
                    'Mantenga una base de datos por servicio solo cuando el equipo pueda operar transacciones distribuidas o sagas.',
                    'API Gateway + service mesh añaden complejidad; para 2-3 servicios, un ALB con rutas por path suele bastar.',
                ),
            ],
            [
                'title' => 'Cómo preparar tu carrera tech en 2026',
                'category' => 'ia',
                'image' => 'blog-career',
                'tags' => ['IA', 'Cloud', 'DevOps'],
                'featured' => false,
                'weeks_ago' => 12,
                'excerpt' => 'Cloud, seguridad e IA aplicada: las tres palancas que más valoran las empresas peruanas.',
                'content' => $this->paragraphs(
                    'La demanda crece en perfiles full-stack con experiencia cloud y nociones de seguridad. Certificaciones AWS/Azure ayudan, pero proyectos reales pesan más en entrevistas.',
                    'Aprenda a documentar decisiones técnicas y a comunicar trade-offs a negocio. Soft skills diferencian seniors de mid-level.',
                    'OPEN9 Academy ofrece rutas prácticas en Laravel, cloud y DevOps con proyectos que puedes mostrar en portafolio.',
                ),
            ],
            [
                'title' => 'Monitoreo con Grafana y Prometheus en cloud híbrido',
                'category' => 'operacion',
                'image' => 'blog-monitoring',
                'tags' => ['AWS', 'Azure', 'DevOps'],
                'featured' => false,
                'weeks_ago' => 13,
                'excerpt' => 'Métricas unificadas desde on-premise y multi-cloud en un solo paneo de observabilidad.',
                'content' => $this->paragraphs(
                    'Desplegamos Prometheus agents en cada sitio y centralizamos en Grafana Cloud con remote write. Alertas en PagerDuty con runbooks enlazados.',
                    'SLIs definidos por servicio: disponibilidad, latencia p95 y tasa de error. SLO de 99.9% con presupuesto de error mensual visible al equipo.',
                    'Logs estructurados en JSON facilitan correlación trace-id entre Laravel, nginx y workers de cola.',
                ),
            ],
            [
                'title' => 'Servidores rack: guía de dimensionamiento 2026',
                'category' => 'operacion',
                'image' => 'server-rack',
                'tags' => ['Hardware', 'Cloud'],
                'featured' => false,
                'weeks_ago' => 14,
                'excerpt' => 'CPU, RAM, almacenamiento y redundancia para cargas de virtualización y bases de datos.',
                'content' => $this->paragraphs(
                    'Para virtualización ligera (10-20 VMs), un servidor 2U dual-socket con 256 GB RAM y almacenamiento híbrido SSD/NVMe cubre la mayoría de PYMEs.',
                    'Redundancia de fuentes y RAID 10 en datos críticos no son opcionales en producción. Planifique UPS online con autonomía mínima de 15 minutos.',
                    'OPEN9 asesora en TCO comparando on-premise vs cloud reservado para cargas predecibles de 3+ años.',
                ),
            ],
            [
                'title' => 'WhatsApp no es un CRM: cómo no perder leads el sábado',
                'category' => 'automatizacion',
                'image' => 'chatbot-whatsapp',
                'tags' => ['WhatsApp', 'Automatización', 'Inmobiliarias'],
                'featured' => true,
                'weeks_ago' => 1,
                'excerpt' => 'Si tu mejor vendedor apaga el celular a las 18:00, el lead del fin de semana ya no es tuyo. Un agente cubre ese hueco.',
                'content' => $this->paragraphs(
                    'El error no es usar WhatsApp. Es usarlo como bandeja personal en vez de como canal de la empresa.',
                    'Cada mensaje debe crear una ficha, responder lo frecuente y dejar al humano solo las visitas calificadas.',
                    'Mide tiempo de primera respuesta el sábado. Si supera 30 minutos, estás regalando consultas a la competencia.',
                ),
            ],
            [
                'title' => 'El dashboard que un dueño sí mira a las 9:00',
                'category' => 'operacion',
                'image' => 'dashboard-kpi',
                'tags' => ['Dashboards', 'Comercios', 'Restaurantes'],
                'featured' => true,
                'weeks_ago' => 2,
                'excerpt' => 'Tres números: lo que se vendió ayer, lo que está parado y lo que no cuadra. El resto es ruido.',
                'content' => $this->paragraphs(
                    'Un panel con 40 widgets no se usa. Un panel con caja, stock crítico y no-shows sí.',
                    'Empieza por una pantalla que el dueño abre con café. Si no responde “¿cómo nos fue ayer?”, sobra.',
                    'La IA puede predecir demanda después. Primero el número verdadero, luego el modelo.',
                ),
            ],
            [
                'title' => 'Clínicas: lista de espera que sí llena el hueco',
                'category' => 'ia',
                'image' => 'clinic-desk',
                'tags' => ['Clínicas', 'IA', 'Chatbots'],
                'featured' => false,
                'weeks_ago' => 3,
                'excerpt' => 'Confirmar no basta. Cuando alguien cancela, alguien de la lista debe ocupar el cupo en minutos, no al día siguiente.',
                'content' => $this->paragraphs(
                    'El recordatorio baja inasistencias. La lista de espera recupera la hora perdida.',
                    'Un flujo simple: cancelación → oferta al siguiente de la especialidad → confirmación en WhatsApp.',
                    'Ahí entra la IA: elegir a quién ofrecer primero según historial de asistencia, no al azar.',
                ),
            ],
            [
                'title' => 'Estudios contables: deja de perseguir PDFs',
                'category' => 'operacion',
                'image' => 'documents',
                'tags' => ['Automatización', 'Integraciones'],
                'featured' => false,
                'weeks_ago' => 5,
                'excerpt' => 'Un enlace por cliente, un recordatorio de fecha y un tablero de quién está incompleto. Eso ya ahorra 10 horas a la semana.',
                'content' => $this->paragraphs(
                    'El dolor no es “falta un ERP”. Es que 80 clientes mandan WhatsApp con fotos borrosas el último día.',
                    'Cada cliente tiene una carpeta y un estado: completo, faltante, vencido. El estudio ve la cola, no el inbox.',
                    'Cuando eso corre, puedes conciliar. Automatizar el caos de archivos solo acelera el caos.',
                ),
            ],
        ];

        foreach ($posts as $index => $data) {
            $slug = Str::slug($data['title']);
            $category = $categories->get($data['category']) ?? $defaultCategory;

            $post = BlogPost::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $editor->id,
                    'blog_category_id' => $category->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'main_image' => $this->referenceImage($data['image'], 1200, 675),
                    'video_url' => null,
                    'gallery' => $this->referenceGallery($data['image'], 3, 800, 500),
                    'status' => 'published',
                    'is_featured' => $data['featured'],
                    'views_count' => 420 + ($index * 38),
                    'reading_time' => 5 + ($index % 5),
                    'seo_title' => $data['title'].' | Blog OPEN9',
                    'seo_description' => $data['excerpt'],
                    'seo_keywords' => 'open9, '.Str::slug($category->name, ', '),
                    'published_at' => now()->subWeeks($data['weeks_ago']),
                ],
            );

            $tagIds = BlogTag::query()->whereIn('name', $data['tags'])->pluck('id');
            if ($tagIds->isNotEmpty()) {
                $post->tags()->sync($tagIds->all());
            }
        }

        $validSlugs = collect($posts)->map(fn (array $data): string => Str::slug($data['title']))->all();
        BlogPost::query()
            ->whereNotIn('slug', $validSlugs)
            ->update(['status' => 'draft', 'is_featured' => false]);
    }
}
