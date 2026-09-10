<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Services\StorageConfigService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        app(StorageConfigService::class)->registerGcsDriver();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        $this->registerSiteCacheClearing();
    }

    protected function registerSiteCacheClearing(): void
    {
        $models = [
            \App\Models\SiteBranding::class,
            \App\Models\FooterLinkGroup::class,
            \App\Models\FooterLink::class,
            \App\Models\SocialLink::class,
            \App\Models\HomeStat::class,
            \App\Models\HomeHeroPanelSetting::class,
            \App\Models\HomeHeroPanelStat::class,
            \App\Models\HomeHeroPanelPill::class,
            \App\Models\HomeHeroShowcaseSetting::class,
            \App\Models\HomeHeroShowcaseCard::class,
            \App\Models\HomeFeatureCard::class,
            \App\Models\HomeWorkflowStep::class,
            \App\Models\HomeQuickLink::class,
            \App\Models\HomePricingPlan::class,
            \App\Models\HomeSectionSetting::class,
            \App\Models\LegalPage::class,
            \App\Models\AiChatSetting::class,
            \App\Models\Setting::class,
        ];

        foreach ($models as $model) {
            $model::saved(fn () => app(\App\Services\SiteConfigService::class)->clearCache());
            $model::deleted(fn () => app(\App\Services\SiteConfigService::class)->clearCache());
        }
    }
}
