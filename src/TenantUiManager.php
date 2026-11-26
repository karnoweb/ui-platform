<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi;

use Illuminate\Contracts\Container\Container;
use KarnoWeb\TenantUi\Registries\DataProviderRegistry;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;
use KarnoWeb\TenantUi\Services\BladeRenderer;

/**
 * Lightweight gateway; resolves services lazily from the container.
 */
final class TenantUiManager
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function for(string $tenantKey): TenantUiTenantScope
    {
        return new TenantUiTenantScope($tenantKey, $this->container);
    }

    public function themes(): ThemeRegistry
    {
        return $this->container->make(ThemeRegistry::class);
    }

    public function presets(): PresetRegistry
    {
        return $this->container->make(PresetRegistry::class);
    }

    public function variants(): VariantRegistry
    {
        return $this->container->make(VariantRegistry::class);
    }

    public function pageTypes(): PageTypeRegistry
    {
        return $this->container->make(PageTypeRegistry::class);
    }

    public function dataProviders(): DataProviderRegistry
    {
        return $this->container->make(DataProviderRegistry::class);
    }

    public function render(): BladeRenderer
    {
        return $this->container->make(BladeRenderer::class);
    }

    public function version(): string
    {
        return '1.1.0';
    }
}
