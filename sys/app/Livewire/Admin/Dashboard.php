<?php

namespace App\Livewire\Admin;

use App\Enums\ContactStatus;
use App\Enums\PublishStatus;
use App\Enums\RecordStatus;
use App\Models\AiChatSetting;
use App\Models\BlogPost;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\SocialLoginSetting;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function greeting(): string
    {
        $hour = (int) now()->format('G');
        $salute = match (true) {
            $hour < 12 => 'Buenos días',
            $hour < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        $name = trim((string) (auth()->user()?->name ?? ''));

        if ($name === '') {
            return $salute;
        }

        return $salute.', '.Str::of($name)->before(' ')->toString();
    }

    public function todayLabel(): string
    {
        return Str::ucfirst(now()->locale('es')->translatedFormat('l d \d\e F'));
    }

    /**
     * @return list<array{title: string, hint: string, href: string, icon: string}>
     */
    public function attentionItems(): array
    {
        return once(function (): array {
            $snapshot = $this->snapshot();
            $items = [];

            if ($snapshot['unpaid_orders'] > 0) {
                $href = $this->adminHref('orders.view', 'admin.orders.index');

                if (filled($href)) {
                    $items[] = [
                        'title' => $this->spanishCount($snapshot['unpaid_orders'], 'pedido pendiente de pago', 'pedidos pendientes de pago'),
                        'hint' => 'Hay cobros de tienda sin confirmar.',
                        'href' => $href,
                        'icon' => 'receipt-percent',
                    ];
                }
            }

            if ($snapshot['new_contacts'] > 0) {
                $href = $this->adminHref('contacts.view', 'admin.contacts.index');

                if (filled($href)) {
                    $items[] = [
                        'title' => $this->spanishCount($snapshot['new_contacts'], 'contacto nuevo por revisar', 'contactos nuevos por revisar'),
                        'hint' => 'Mensajes del formulario público sin atender.',
                        'href' => $href,
                        'icon' => 'envelope',
                    ];
                }
            }

            return $items;
        });
    }

    /**
     * @return list<array{label: string, value: int|string, hint?: string, href?: string|null, icon?: string, tone?: string}>
     */
    public function highlightMetrics(): array
    {
        $snapshot = $this->snapshot();

        return [
            [
                'label' => 'Ingresos tienda',
                'value' => $this->money($snapshot['store_revenue']),
                'hint' => 'Pedidos con pago confirmado',
                'href' => $this->adminHref('orders.view', 'admin.orders.index'),
                'icon' => 'banknotes',
                'tone' => 'default',
            ],
            [
                'label' => 'Pedidos por cobrar',
                'value' => $snapshot['unpaid_orders'],
                'hint' => $snapshot['today_orders'] > 0
                    ? $this->spanishCount($snapshot['today_orders'], 'pedido hoy', 'pedidos hoy')
                    : 'Ningún pedido nuevo hoy',
                'href' => $this->adminHref('orders.view', 'admin.orders.index'),
                'icon' => 'receipt-percent',
                'tone' => $snapshot['unpaid_orders'] > 0 ? 'attention' : 'default',
            ],
            [
                'label' => 'Contactos nuevos',
                'value' => $snapshot['new_contacts'],
                'hint' => $snapshot['today_contacts'] > 0
                    ? $this->spanishCount($snapshot['today_contacts'], 'mensaje hoy', 'mensajes hoy')
                    : 'Sin mensajes nuevos hoy',
                'href' => $this->adminHref('contacts.view', 'admin.contacts.index'),
                'icon' => 'envelope',
                'tone' => $snapshot['new_contacts'] > 0 ? 'attention' : 'default',
            ],
            [
                'label' => 'Clientes registrados',
                'value' => $snapshot['active_clients'],
                'hint' => $snapshot['today_clients'] > 0
                    ? $this->spanishCount($snapshot['today_clients'], 'alta hoy', 'altas hoy')
                    : 'Cuentas activas de la tienda',
                'href' => $this->adminHref('clients.view', 'admin.clients.index'),
                'icon' => 'users',
                'tone' => 'default',
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: int|string, hint?: string, href?: string|null, icon?: string}>
     */
    public function storeMetrics(): array
    {
        $snapshot = $this->snapshot();

        return [
            [
                'label' => 'Productos publicados',
                'value' => $snapshot['published_products'],
                'href' => $this->adminHref('products.view', 'admin.products.index'),
                'icon' => 'shopping-bag',
            ],
            [
                'label' => 'Pedidos totales',
                'value' => $snapshot['orders_total'],
                'href' => $this->adminHref('orders.view', 'admin.orders.index'),
                'icon' => 'queue-list',
            ],
            [
                'label' => 'Pedidos pagados',
                'value' => $snapshot['paid_orders'],
                'href' => $this->adminHref('orders.view', 'admin.orders.index'),
                'icon' => 'check-circle',
            ],
            [
                'label' => 'Pasarela MercadoPago',
                'value' => $snapshot['mercadopago_enabled'] ? 'Activa' : 'Inactiva',
                'hint' => $snapshot['mercadopago_hint'],
                'href' => $this->adminHref('payment-settings.view', 'admin.payment-settings.index'),
                'icon' => 'credit-card',
            ],
            [
                'label' => 'Login con Google',
                'value' => $snapshot['google_enabled'] ? 'Activo' : 'Inactivo',
                'hint' => $snapshot['google_hint'],
                'href' => $this->adminHref('social-login.view', 'admin.social-login.index'),
                'icon' => 'globe-alt',
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: int|string, hint?: string, href?: string|null, icon?: string}>
     */
    public function contentMetrics(): array
    {
        $snapshot = $this->snapshot();

        return [
            [
                'label' => 'Proyectos publicados',
                'value' => $snapshot['published_projects'],
                'href' => $this->adminHref('projects.view', 'admin.projects.index'),
                'icon' => 'folder',
            ],
            [
                'label' => 'Servicios activos',
                'value' => $snapshot['published_services'],
                'href' => $this->adminHref('services.view', 'admin.services.index'),
                'icon' => 'wrench-screwdriver',
            ],
            [
                'label' => 'Artículos publicados',
                'value' => $snapshot['published_posts'],
                'href' => $this->adminHref('blog.view', 'admin.blog.index'),
                'icon' => 'newspaper',
            ],
            [
                'label' => 'Chat IA',
                'value' => $snapshot['chat_enabled'] ? 'Activo' : 'Inactivo',
                'hint' => $snapshot['chat_hint'],
                'href' => $this->adminHref('ai-chat.view', 'admin.ai-chat.index'),
                'icon' => 'chat-bubble-left-right',
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: int|string, href?: string|null, icon?: string}>
     */
    public function academyMetrics(): array
    {
        $snapshot = $this->snapshot();

        return [
            [
                'label' => 'Cursos publicados',
                'value' => $snapshot['published_courses'],
                'href' => $this->adminHref('courses.view', 'admin.courses.index'),
                'icon' => 'academic-cap',
            ],
            [
                'label' => 'Inscripciones',
                'value' => $snapshot['enrollments'],
                'href' => $this->adminHref('enrollments.view', 'admin.enrollments.index'),
                'icon' => 'user-plus',
            ],
            [
                'label' => 'Pagos aprobados',
                'value' => $snapshot['approved_payments'],
                'href' => $this->adminHref('payments.view', 'admin.payments.index'),
                'icon' => 'banknotes',
            ],
            [
                'label' => 'Ingresos academia',
                'value' => $this->money($snapshot['academy_revenue']),
                'href' => $this->adminHref('payments.view', 'admin.payments.index'),
                'icon' => 'chart-bar',
            ],
        ];
    }

    /**
     * @return list<array{label: string, route: string, icon: string}>
     */
    public function quickLinks(): array
    {
        $links = [
            ['label' => 'Pedidos', 'route' => 'admin.orders.index', 'icon' => 'receipt-percent', 'permission' => 'orders.view'],
            ['label' => 'Contactos', 'route' => 'admin.contacts.index', 'icon' => 'envelope', 'permission' => 'contacts.view'],
            ['label' => 'Productos', 'route' => 'admin.products.index', 'icon' => 'shopping-bag', 'permission' => 'products.view'],
            ['label' => 'Clientes', 'route' => 'admin.clients.index', 'icon' => 'users', 'permission' => 'clients.view'],
            ['label' => 'Cursos', 'route' => 'admin.courses.index', 'icon' => 'academic-cap', 'permission' => 'courses.view'],
            ['label' => 'Identidad y marca', 'route' => 'admin.site-branding.index', 'icon' => 'sparkles', 'permission' => 'site-branding.view'],
        ];

        return collect($links)
            ->filter(fn (array $link): bool => auth()->user()?->can($link['permission']) ?? false)
            ->filter(fn (array $link): bool => Route::has($link['route']))
            ->map(fn (array $link): array => [
                'label' => $link['label'],
                'route' => $link['route'],
                'icon' => $link['icon'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{title: string, subtitle: string, badge: string, badge_color: string, when: string}>
     */
    public function latestContactRows(): array
    {
        return once(fn (): array => Contact::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'status', 'created_at'])
            ->map(fn (Contact $contact): array => [
                'title' => $contact->name,
                'subtitle' => $contact->email,
                'badge' => match ($contact->status) {
                    ContactStatus::New => 'Nuevo',
                    ContactStatus::Read => 'Leído',
                    ContactStatus::Answered => 'Respondido',
                    ContactStatus::Archived => 'Archivado',
                    default => '—',
                },
                'badge_color' => match ($contact->status) {
                    ContactStatus::New => 'blue',
                    ContactStatus::Answered => 'green',
                    ContactStatus::Archived => 'zinc',
                    default => 'amber',
                },
                'when' => $contact->created_at?->diffForHumans() ?? '',
            ])
            ->all());
    }

    /**
     * @return list<array{title: string, subtitle: string, badge: string, badge_color: string, when: string}>
     */
    public function latestOrderRows(): array
    {
        return once(fn (): array => Order::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'order_code', 'buyer_name', 'total', 'currency', 'payment_status', 'created_at'])
            ->map(fn (Order $order): array => [
                'title' => $order->order_code ?: ('#'.$order->id),
                'subtitle' => trim($order->buyer_name.' · '.$this->money((float) $order->total, $order->currency ?: 'PEN')),
                'badge' => match ($order->payment_status) {
                    'paid' => 'Pagado',
                    'pending' => 'En proceso',
                    'failed' => 'Fallido',
                    default => 'Pendiente',
                },
                'badge_color' => match ($order->payment_status) {
                    'paid' => 'green',
                    'pending' => 'amber',
                    'failed' => 'red',
                    default => 'red',
                },
                'when' => $order->created_at?->diffForHumans() ?? '',
            ])
            ->all());
    }

    /**
     * @return list<array{title: string, subtitle: string, when: string}>
     */
    public function latestClientRows(): array
    {
        return once(fn (): array => Client::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(fn (Client $client): array => [
                'title' => $client->name,
                'subtitle' => $client->email,
                'when' => $client->created_at?->diffForHumans() ?? '',
            ])
            ->all());
    }

    /**
     * @return array{labels: list<string>, contacts: list<int>, clients: list<int>, orders: list<int>, max: float}
     */
    public function monthlySeries(): array
    {
        return once(function (): array {
            $months = collect(range(5, 0))
                ->map(fn (int $monthsAgo): CarbonImmutable => now()->toImmutable()->subMonths($monthsAgo)->startOfMonth());

            $start = $months->first() ?? now()->toImmutable()->startOfMonth();
            $end = now()->toImmutable()->endOfMonth();
            $monthExpression = $this->monthKeyExpression();

            $contacts = $this->monthlyCounts(Contact::query(), $start, $end, $monthExpression);
            $clients = $this->monthlyCounts(Client::query(), $start, $end, $monthExpression);
            $orders = $this->monthlyCounts(Order::query(), $start, $end, $monthExpression);

            $labels = [];
            $contactCounts = [];
            $clientCounts = [];
            $orderCounts = [];

            foreach ($months as $month) {
                $key = $month->format('Y-m');
                $labels[] = Str::ucfirst($month->locale('es')->translatedFormat('M'));
                $contactCounts[] = $contacts[$key] ?? 0;
                $clientCounts[] = $clients[$key] ?? 0;
                $orderCounts[] = $orders[$key] ?? 0;
            }

            return [
                'labels' => $labels,
                'contacts' => $contactCounts,
                'clients' => $clientCounts,
                'orders' => $orderCounts,
                'max' => max(1, ...$contactCounts, ...$clientCounts, ...$orderCounts),
            ];
        });
    }

    /**
     * @return array{
     *     published_products: int,
     *     orders_total: int,
     *     unpaid_orders: int,
     *     paid_orders: int,
     *     store_revenue: float,
     *     active_clients: int,
     *     today_orders: int,
     *     today_contacts: int,
     *     today_clients: int,
     *     published_projects: int,
     *     published_services: int,
     *     published_posts: int,
     *     new_contacts: int,
     *     published_courses: int,
     *     enrollments: int,
     *     approved_payments: int,
     *     academy_revenue: float,
     *     mercadopago_enabled: bool,
     *     mercadopago_hint: string,
     *     google_enabled: bool,
     *     google_hint: string,
     *     chat_enabled: bool,
     *     chat_hint: string
     * }
     */
    private function snapshot(): array
    {
        return once(function (): array {
            $payments = PaymentSetting::current();
            $social = SocialLoginSetting::current();
            $chat = AiChatSetting::query()->first();
            $paidOrders = Order::query()->where('payment_status', 'paid');
            $today = now()->toDateString();

            return [
                'published_products' => Product::query()->where('status', PublishStatus::Published)->count(),
                'orders_total' => Order::query()->count(),
                'unpaid_orders' => Order::query()->where('payment_status', 'unpaid')->count(),
                'paid_orders' => (clone $paidOrders)->count(),
                'store_revenue' => (float) (clone $paidOrders)->sum('total'),
                'active_clients' => Client::query()->where('status', RecordStatus::Active)->count(),
                'today_orders' => Order::query()->whereDate('created_at', $today)->count(),
                'today_contacts' => Contact::query()->whereDate('created_at', $today)->count(),
                'today_clients' => Client::query()->whereDate('created_at', $today)->count(),
                'published_projects' => Project::query()->where('status', 'published')->count(),
                'published_services' => Service::query()->where('status', 'published')->count(),
                'published_posts' => BlogPost::query()->where('status', 'published')->count(),
                'new_contacts' => Contact::query()->where('status', ContactStatus::New)->count(),
                'published_courses' => Course::query()->where('status', 'published')->count(),
                'enrollments' => CourseEnrollment::query()->count(),
                'approved_payments' => Payment::query()->where('status', 'approved')->count(),
                'academy_revenue' => (float) Payment::query()->where('status', 'approved')->sum('amount'),
                'mercadopago_enabled' => (bool) $payments->is_enabled,
                'mercadopago_hint' => $payments->is_enabled
                    ? strtoupper((string) $payments->mode).' · Checkout Bricks'
                    : 'Cobros deshabilitados en la tienda',
                'google_enabled' => $social->googleEnabled(),
                'google_hint' => $social->googleEnabled() ? 'Disponible en /ingresar' : 'Sin credenciales configuradas',
                'chat_enabled' => (bool) ($chat?->is_enabled),
                'chat_hint' => $chat?->is_enabled
                    ? strtoupper((string) ($chat->provider ?? 'gemini')).' · FAB en el sitio'
                    : 'Asistente oculto en el frontend',
            ];
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @return array<string, int>
     */
    private function monthlyCounts(Builder $query, DateTimeInterface $start, DateTimeInterface $end, string $monthExpression): array
    {
        return $query
            ->whereBetween('created_at', [$start, $end])
            ->toBase()
            ->selectRaw($monthExpression.' as month_key')
            ->selectRaw('count(*) as aggregate')
            ->groupByRaw($monthExpression)
            ->pluck('aggregate', 'month_key')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();
    }

    private function monthKeyExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };
    }

    private function adminHref(string $permission, string $route): ?string
    {
        if (! Route::has($route) || ! (auth()->user()?->can($permission) ?? false)) {
            return null;
        }

        return route($route);
    }

    private function money(float $amount, string $currency = 'PEN'): string
    {
        return $currency.' '.number_format($amount, 2);
    }

    private function spanishCount(int $count, string $singular, string $plural): string
    {
        return $count === 1 ? '1 '.$singular : $count.' '.$plural;
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard');
    }
}
