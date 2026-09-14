<?php

namespace App\Providers;

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
use App\Models\HomeSectionSetting;
use App\Models\HomeStat;
use App\Models\HomeWorkflowStep;
use App\Models\LegalPage;
use App\Models\Setting;
use App\Models\SiteBranding;
use App\Models\SocialLink;
use App\Services\SiteConfigService;
use App\Services\StorageConfigService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $configuredPublicPath = $this->app->make('config')->get('app.public_path');

        if (is_string($configuredPublicPath) && $configuredPublicPath !== '' && is_dir($configuredPublicPath)) {
            $this->app->usePublicPath($configuredPublicPath);

            return;
        }

        $this->app->usePublicPath(dirname($this->app->basePath()));
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

        URL::forceHttps($this->app->isProduction());

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
            SiteBranding::class,
            FooterLinkGroup::class,
            FooterLink::class,
            SocialLink::class,
            HomeStat::class,
            HomeHeroPanelSetting::class,
            HomeHeroPanelStat::class,
            HomeHeroPanelPill::class,
            HomeHeroShowcaseSetting::class,
            HomeHeroShowcaseCard::class,
            HomeFeatureCard::class,
            HomeWorkflowStep::class,
            HomeQuickLink::class,
            HomePricingPlan::class,
            HomeSectionSetting::class,
            LegalPage::class,
            AiChatSetting::class,
            Setting::class,
        ];

        foreach ($models as $model) {
            $model::saved(fn () => app(SiteConfigService::class)->clearCache());
            $model::deleted(fn () => app(SiteConfigService::class)->clearCache());
        }
    }
}
