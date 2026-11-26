<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Support;

use Illuminate\Contracts\Container\Container;
use KarnoWeb\TenantUi\Contracts\ModuleResolverContract;
use KarnoWeb\TenantUi\Exceptions\ConfigurationException;

final readonly class ModuleResolverGate
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function enabled(string $tenantKey, string $moduleKey): bool
    {
        if (! $this->container->bound(ModuleResolverContract::class)) {
            throw new ConfigurationException(
                'Tenant UI module resolver is not configured. Bind '
                .ModuleResolverContract::class
                .' in config/tenant-ui.php.'
            );
        }

        return $this->container
            ->make(ModuleResolverContract::class)
            ->enabled($tenantKey, $moduleKey);
    }
}
