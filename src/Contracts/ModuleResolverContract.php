<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Contracts;

interface ModuleResolverContract
{
    public function enabled(string $tenantKey, string $moduleKey): bool;
}
