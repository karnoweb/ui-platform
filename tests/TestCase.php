<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests;

use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Contracts\ModuleResolverContract;
use KarnoWeb\TenantUi\TenantUiManager;
use KarnoWeb\TenantUi\TenantUiServiceProvider;
use KarnoWeb\TenantUi\TenantUiTenantScope;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TenantUiServiceProvider::class,
            TestDefinitionsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.default', 'array');

        $app['config']->set('tenant-ui.defaults.components', [
            'header' => 'header-default',
            'footer' => 'footer-default',
        ]);

        $app->singleton(ModuleResolverContract::class, TestModuleResolver::class);
        $app->singleton(LocaleManagerContract::class, TestLocaleManager::class);
    }

    protected function tenantUi(string $tenantKey = 'tenant-1'): TenantUiTenantScope
    {
        return app(TenantUiManager::class)->for($tenantKey);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
