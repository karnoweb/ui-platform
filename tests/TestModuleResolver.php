<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests;

use KarnoWeb\TenantUi\Contracts\ModuleResolverContract;

final class TestModuleResolver implements ModuleResolverContract
{
    /** @var array<string, bool> */
    public static array $modules = [
        'search' => true,
        'stories' => true,
    ];

    public function enabled(string $tenantKey, string $moduleKey): bool
    {
        return self::$modules[$moduleKey] ?? false;
    }
}
