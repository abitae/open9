<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Enums\RecordStatus;
use App\Models\AiChatSetting;
use App\Models\BlogPost;
use App\Models\FooterLinkGroup;
use App\Models\HomeFeatureCard;
use App\Models\HomeHeroPanelPill;
use App\Models\HomeHeroPanelSetting;
use App\Models\HomeHeroPanelStat;
use App\Models\HomeHeroShowcaseCard;
use App\Models\HomePricingPlan;
use App\Models\HomeQuickLink;
use App\Models\HomeSectionSetting;
use App\Models\HomeStat;
use App\Models\HomeWorkflowStep;
use App\Models\LegalPage;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SiteBranding;
use App\Models\SocialLink;
use App\Models\SocialLoginSetting;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class SiteConfigService
{
    public function __construct(
        private readonly MediaStorageService $media,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function sitePayload(): array
    {
        return Cache::remember('api.site', 600, function (): array {
            $branding = SiteBranding::query()->firstOrCreate(['id' => 1], [
                'site_name' => 'Open9',
                'tagline' => 'Expertos en automatización e inteligencia artificial',
            ]);

            $chat = AiChatSetting::query()->firstOrCreate(['id' => 1], [
                'is_enabled' => true,
                'fab_label' => 'Red en vivo',
                'welcome_message' => 'Hola, soy el asistente de OPEN9. Pregúntame cómo automatizar tu empresa.',
                'model' => 'gemini-2.0-flash',
            ]);

            $payments = PaymentSetting::current();
            $social = SocialLoginSetting::current();

            return [
                'branding' => [
                    'site_name' => filled($branding->site_name) ? $branding->site_name : null,
                    'tagline' => $branding->tagline,
                    'logo_url' => $this->media->url($branding->logo_path) ?? $this->media->url('/logo_normal.png'),
                    'logo_dark_url' => $this->media->url($branding->logo_dark_path) ?? $this->media->url('/logo_black.png'),
                    'favicon_url' => $this->media->url($branding->favicon_path) ?? $this->media->url('/favicon.png'),
                    'hero' => [
                        'title' => $branding->hero_title,
                        'subtitle' => $branding->hero_subtitle,
                        'cta_primary' => [
                            'label' => $branding->hero_cta_primary_label,
                            'url' => $branding->hero_cta_primary_url,
                        ],
                        'cta_secondary' => [
                            'label' => $branding->hero_cta_secondary_label,
                            'url' => $branding->hero_cta_secondary_url,
                        ],
                    ],
                    'background_video_url' => $this->media->url($branding->background_video_url),
                    'footer_description' => $branding->footer_description,
                    'copyright_text' => $branding->copyright_text,
                    'seo_description' => $branding->seo_description,
                ],
                'contact' => [
                    'email' => $branding->contact_email,
                    'phone' => $branding->contact_phone,
                    'address' => $branding->contact_address,
                    'website_url' => $branding->website_url,
                ],
                'footer_groups' => FooterLinkGroup::query()
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->with(['links' => fn ($q) => $q->orderBy('sort_order')])
                    ->get()
                    ->map(fn (FooterLinkGroup $group): array => [
                        'title' => $group->title,
                        'links' => $group->links->map(fn ($link): array => [
                            'label' => $link->label,
                            'url' => $link->url,
                            'is_external' => $link->is_external,
                        ])->all(),
                    ])->all(),
                'social_links' => SocialLink::query()
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->get(['platform', 'url'])
                    ->map(fn (SocialLink $link): array => [
                        'platform' => $link->platform,
                        'url' => $link->url,
                    ])
                    ->values()
                    ->all(),
                'chat' => [
                    'is_enabled' => $chat->is_enabled,
                    'fab_label' => $chat->fab_label,
                    'welcome_message' => $chat->welcome_message,
                ],
                'store' => [
                    'usd_pen_rate' => $this->usdPenRate(),
                ],
                'payments' => [
                    'provider' => $payments->provider,
                    'enabled' => (bool) $payments->is_enabled && $payments->resolvedAccessToken() !== null,
                    'public_key' => $payments->resolvedPublicKey(),
                    'currency' => strtoupper($payments->currency ?: 'PEN'),
                    'mode' => $payments->mode,
                ],
                'auth' => [
                    'google_enabled' => $social->googleEnabled(),
                ],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function homePayload(): array
    {
        return Cache::remember('api.home', 600, function (): array {
            $panel = HomeHeroPanelSetting::query()->firstOrCreate(['id' => 1], [
                'badge_label' => 'Expertos en automatización · open9.dev',
                'headline_pre' => 'Expertos en',
                'headline_highlight' => 'automatización',
                'headline_subtitle' => 'Transformamos procesos manuales en',
                'headline_subtitle_highlight' => 'soluciones inteligentes',
                'show_site_name_chip' => true,
                'description' => 'Diseñamos automatizaciones, inteligencia artificial y software a medida que impulsan tu negocio: ahorras tiempo, reduces costos y aumentan resultados.',
                'cta_label' => 'Llevar mi empresa al siguiente nivel',
                'cta_url' => '/contacto',
                'cta_icon' => 'Rocket',
                'quote_kicker' => 'Automatización · IA · Software a medida',
                'quote_primary' => 'Tecnología que impulsa',
                'quote_secondary' => 'tu crecimiento.',
                'quote_footer' => 'www.open9.dev',
            ]);

            return [
                'hero_panel' => [
                    'badge_label' => $panel->badge_label,
                    'headline' => [
                        'pre' => $panel->headline_pre,
                        'highlight' => $panel->headline_highlight,
                        'subtitle' => $panel->headline_subtitle,
                        'subtitle_highlight' => $panel->headline_subtitle_highlight,
                        'show_site_name_chip' => (bool) $panel->show_site_name_chip,
                    ],
                    'description' => $panel->description,
                    'cta' => [
                        'label' => $panel->cta_label,
                        'url' => $panel->cta_url,
                        'icon' => $panel->cta_icon,
                    ],
                    'quote' => [
                        'kicker' => $panel->quote_kicker,
                        'primary' => $panel->quote_primary,
                        'secondary' => $panel->quote_secondary,
                        'footer' => $panel->quote_footer,
                    ],
                    'media_type' => $panel->media_type ?? 'none',
                    'image_url' => filled($panel->image_path ?? null)
                        ? $this->media->url($panel->image_path)
                        : null,
                    'video_url' => filled($panel->video_path ?? null)
                        ? $this->media->url($panel->video_path)
                        : null,
                    'stats' => HomeHeroPanelStat::query()
                        ->where('is_visible', true)
                        ->where('status', RecordStatus::Active)
                        ->orderBy('sort_order')
                        ->get(['value', 'suffix', 'label'])
                        ->map(fn (HomeHeroPanelStat $stat): array => [
                            'value' => $stat->value,
                            'suffix' => $stat->suffix,
                            'label' => $stat->label,
                        ])
                        ->values()
                        ->all(),
                    'pills' => HomeHeroPanelPill::query()
                        ->where('is_visible', true)
                        ->where('status', RecordStatus::Active)
                        ->orderBy('sort_order')
                        ->pluck('label')
                        ->values()
                        ->all(),
                ],
                'hero_showcase' => [
                    'cards' => HomeHeroShowcaseCard::query()
                        ->where('is_visible', true)
                        ->where('status', RecordStatus::Active)
                        ->orderBy('sort_order')
                        ->get(['layout', 'title', 'description', 'icon', 'media_type', 'image_path', 'video_path'])
                        ->map(fn (HomeHeroShowcaseCard $card): array => [
                            'layout' => $card->layout,
                            'title' => $card->title,
                            'description' => $card->description,
                            'icon' => $card->icon,
                            'media_type' => $card->media_type ?? 'none',
                            'image_url' => ($card->media_type ?? 'none') === 'image'
                                ? $this->media->url($card->image_path)
                                : null,
                            'video_url' => ($card->media_type ?? 'none') === 'video'
                                ? $this->media->url($card->video_path)
                                : null,
                        ])
                        ->values()
                        ->all(),
                ],
                'section_headers' => HomeSectionSetting::query()
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->get(['section_key', 'label', 'title', 'title_highlight', 'description', 'cta_label', 'cta_url'])
                    ->mapWithKeys(fn (HomeSectionSetting $section): array => [
                        $section->section_key => [
                            'label' => $section->label,
                            'title' => $section->title,
                            'title_highlight' => $section->title_highlight,
                            'description' => $section->description,
                            'cta_label' => $section->cta_label,
                            'cta_url' => $section->cta_url,
                            'is_visible' => true,
                        ],
                    ])
                    ->all(),
                'stats' => HomeStat::query()
                    ->where('is_visible', true)
                    ->where('status', RecordStatus::Active)
                    ->orderBy('sort_order')
                    ->get(['value', 'suffix', 'title', 'icon'])
                    ->map(fn (HomeStat $stat): array => [
                        'value' => $stat->value,
                        'suffix' => $stat->suffix,
                        'title' => $stat->title,
                        'icon' => $stat->icon,
                    ])
                    ->values()
                    ->all(),
                'feature_cards' => HomeFeatureCard::query()
                    ->where('is_visible', true)
                    ->where('status', RecordStatus::Active)
                    ->orderBy('sort_order')
                    ->get(['card_type', 'client_type', 'title', 'description', 'icon'])
                    ->map(fn (HomeFeatureCard $card): array => [
                        'card_type' => $card->card_type,
                        'client_type' => $card->client_type,
                        'title' => $card->title,
                        'description' => $card->description,
                        'icon' => $card->icon,
                    ])
                    ->values()
                    ->all(),
                'workflow_steps' => HomeWorkflowStep::query()
                    ->where('is_visible', true)
                    ->where('status', RecordStatus::Active)
                    ->orderBy('sort_order')
                    ->get(['step_number', 'title', 'description', 'icon'])
                    ->map(fn (HomeWorkflowStep $step): array => [
                        'step_number' => $step->step_number,
                        'title' => $step->title,
                        'description' => $step->description,
                        'icon' => $step->icon,
                    ])
                    ->values()
                    ->all(),
                'quick_links' => HomeQuickLink::query()
                    ->where('is_visible', true)
                    ->where('status', RecordStatus::Active)
                    ->orderBy('sort_order')
                    ->get(['title', 'description', 'link_url', 'icon'])
                    ->map(fn (HomeQuickLink $link): array => [
                        'title' => $link->title,
                        'description' => $link->description,
                        'link_url' => $link->link_url,
                        'icon' => $link->icon,
                    ])
                    ->values()
                    ->all(),
                'pricing_plans' => HomePricingPlan::query()
                    ->where('is_visible', true)
                    ->where('status', RecordStatus::Active)
                    ->orderBy('sort_order')
                    ->get(['name', 'price', 'period', 'description', 'features', 'cta_text', 'cta_url', 'is_highlighted'])
                    ->map(fn (HomePricingPlan $plan): array => [
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'period' => $plan->period,
                        'description' => $plan->description,
                        'features' => $plan->features ?? [],
                        'cta_text' => $plan->cta_text,
                        'cta_url' => $plan->cta_url,
                        'is_highlighted' => (bool) $plan->is_highlighted,
                    ])
                    ->values()
                    ->all(),
                'testimonials' => Testimonial::query()
                    ->where('status', RecordStatus::Active)
                    ->orderBy('sort_order')
                    ->limit(9)
                    ->get(['name', 'profession', 'company', 'content', 'rating', 'photo'])
                    ->map(fn (Testimonial $t): array => [
                        'quote' => $t->content,
                        'author' => $t->name,
                        'profession' => $t->profession,
                        'company' => $t->company,
                        'role' => trim(($t->profession ?? '').($t->company ? ', '.$t->company : '')),
                        'rating' => (int) ($t->rating ?? 5),
                        'photo_url' => $this->media->url($t->photo),
                    ])->all(),
            ];
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function legalPage(string $slug): ?array
    {
        $page = LegalPage::query()
            ->where('slug', $slug)
            ->where('status', PublishStatus::Published)
            ->first();

        if ($page === null) {
            return null;
        }

        return [
            'slug' => $page->slug,
            'title' => $page->title,
            'blocks' => $page->blocks ?? [],
        ];
    }

    public function clearCache(): void
    {
        Cache::forget('api.site');
        Cache::forget('api.home');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function blogPosts(): array
    {
        return BlogPost::query()
            ->where('status', PublishStatus::Published)
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (BlogPost $post): array => $this->formatBlogPost($post))
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function blogPost(string $slug): ?array
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('status', PublishStatus::Published)
            ->with(['category', 'author', 'tags'])
            ->first();

        return $post ? $this->formatBlogPost($post, detailed: true) : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function projects(): array
    {
        return Project::query()
            ->where('status', PublishStatus::Published)
            ->with('category')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (Project $project): array => $this->formatProject($project))
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function project(string $slug): ?array
    {
        $project = Project::query()
            ->where('slug', $slug)
            ->where('status', PublishStatus::Published)
            ->with('category')
            ->first();

        return $project ? $this->formatProject($project, detailed: true) : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function services(): array
    {
        return Service::query()
            ->where('status', PublishStatus::Published)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Service $service): array => [
                'id' => (string) $service->id,
                'slug' => $service->slug,
                'title' => $service->title,
                'description' => $service->description,
                'icon' => $service->icon,
                'image_url' => $this->media->url($service->main_image),
                'price' => $service->price_label,
                'features' => $service->features ?? [],
            ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productBrands(): array
    {
        return ProductBrand::query()
            ->where('status', RecordStatus::Active)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ProductBrand $brand): array => $this->formatProductBrand($brand))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function products(?string $brandSlug = null): array
    {
        return Product::query()
            ->where('status', PublishStatus::Published)
            ->with(['category', 'brand'])
            ->when($brandSlug, function (Builder $query) use ($brandSlug): void {
                $query->whereHas(
                    'brand',
                    fn (Builder $brand): Builder => $brand->where('slug', $brandSlug)->where('status', RecordStatus::Active)
                );
            })
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Product $product): array => $this->formatProduct($product))
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function product(string $slug): ?array
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', PublishStatus::Published)
            ->with(['category', 'brand'])
            ->first();

        return $product ? $this->formatProduct($product, detailed: true) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatBlogPost(BlogPost $post, bool $detailed = false): array
    {
        $data = [
            'id' => (string) $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'category' => $post->category?->name ?? '',
            'tags' => $post->relationLoaded('tags') ? $post->tags->pluck('name')->all() : [],
            'date' => $post->published_at?->format('d M Y') ?? '',
            'readTime' => ($post->reading_time ?? 5).' min',
            'author' => $post->author?->name ?? 'Open9',
            'image_url' => $this->media->url($post->main_image),
        ];

        if ($detailed) {
            $data['content'] = $post->content
                ? array_values(array_filter(preg_split('/\r\n|\r|\n/', (string) $post->content)))
                : [];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProject(Project $project, bool $detailed = false): array
    {
        $data = [
            'id' => (string) $project->id,
            'slug' => $project->slug,
            'title' => $project->title,
            'category' => $project->category?->name ?? '',
            'year' => $project->published_at?->format('Y') ?? '',
            'description' => $project->short_description ?? $project->description,
            'tags' => $project->technology_stack ?? [],
            'featured' => (bool) $project->is_featured,
            'image_url' => $this->media->url($project->main_image),
        ];

        if ($detailed) {
            $data['description_full'] = $project->description;
            $data['gallery'] = collect($project->gallery ?? [])
                ->map(fn (string $path): ?string => $this->media->url($path))
                ->filter()
                ->values()
                ->all();
        }

        return $data;
    }

    private function usdPenRate(): float
    {
        $setting = Setting::query()
            ->where('group', 'store')
            ->where('key', 'usd_pen_rate')
            ->value('value');

        $rate = is_numeric($setting) ? (float) $setting : 3.75;

        return max(0.01, $rate);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProduct(Product $product, bool $detailed = false): array
    {
        $data = [
            'id' => (string) $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'category' => $product->category?->name,
            'brand' => $product->brand?->name,
            'brand_slug' => $product->brand?->slug,
            'brand_image_url' => $this->media->url($product->brand?->image),
            'price' => (float) $product->price,
            'currency' => strtoupper($product->currency ?: 'USD'),
            'prices' => $this->productPrices($product),
            'description' => $product->description,
            'rating' => (float) $product->rating,
            'badge' => $product->badge,
            'stock' => $product->stock,
            'image_url' => $this->media->url($product->main_image),
        ];

        if ($detailed) {
            $data['gallery'] = collect($product->gallery ?? [])
                ->map(fn (string $path): ?string => $this->media->url($path))
                ->filter()
                ->values()
                ->all();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProductBrand(ProductBrand $brand): array
    {
        return [
            'id' => (string) $brand->id,
            'slug' => $brand->slug,
            'name' => $brand->name,
            'description' => $brand->description,
            'image_url' => $this->media->url($brand->image),
        ];
    }

    /**
     * @return array{USD: float, PEN: float}
     */
    private function productPrices(Product $product): array
    {
        $rate = $this->usdPenRate();
        $price = (float) $product->price;
        $currency = strtoupper($product->currency ?: 'USD');

        if ($currency === 'PEN') {
            return [
                'USD' => round($price / $rate, 2),
                'PEN' => $price,
            ];
        }

        return [
            'USD' => $price,
            'PEN' => round($price * $rate, 2),
        ];
    }
}
