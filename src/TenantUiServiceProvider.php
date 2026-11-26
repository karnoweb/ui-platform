<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi;

use Illuminate\Support\ServiceProvider;
use KarnoWeb\TenantUi\Console\DoctorCommand;
use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Contracts\ModuleResolverContract;
use KarnoWeb\TenantUi\Contracts\TenantResolverContract;
use KarnoWeb\TenantUi\Registries\DataProviderRegistry;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;
use KarnoWeb\TenantUi\Services\RenderDataMemoizer;
use KarnoWeb\TenantUi\Support\DefaultLocaleManager;
use KarnoWeb\TenantUi\Support\ModuleResolverGate;

final class TenantUiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/tenant-ui.php',
            'tenant-ui'
        );

        $this->app->singleton(ThemeRegistry::class);
        $this->app->singleton(PresetRegistry::class);
        $this->app->singleton(VariantRegistry::class);
        $this->app->singleton(PageTypeRegistry::class);
        $this->app->singleton(DataProviderRegistry::class);
        $this->app->singleton(Services\RuntimeCache::class);
        $this->app->singleton(Services\RouteCacheService::class);
        $this->app->singleton(ModuleResolverGate::class);
        $this->app->scoped(RenderDataMemoizer::class);

        $this->bindConfiguredContract(
            TenantResolverContract::class,
            'tenant-ui.resolvers.tenant'
        );

        $this->bindConfiguredContract(
            ModuleResolverContract::class,
            'tenant-ui.resolvers.modules',
        );

        $this->bindConfiguredContract(
            LocaleManagerContract::class,
            'tenant-ui.resolvers.locale',
            DefaultLocaleManager::class,
        );

        $this->app->singleton('tenant-ui', fn ($app) => new TenantUiManager($app));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tenant-ui.php' => config_path('tenant-ui.php'),
            ], 'tenant-ui-config');

            $this->commands([
                DoctorCommand::class,
            ]);
        }

        if (config('tenant-ui.runtime_api.enabled')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }
    }

    private function bindConfiguredContract(
        string $contract,
        string $configKey,
        ?string $default = null,
    ): void {
        $implementation = config($configKey) ?: $default;

        if (! $implementation) {
            return;
        }

        $this->app->singleton($contract, $implementation);
    }
}
