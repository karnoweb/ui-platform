<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Facades;

use Illuminate\Support\Facades\Facade;
use KarnoWeb\TenantUi\Registries\DataProviderRegistry;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;
use KarnoWeb\TenantUi\Services\BladeRenderer;
use KarnoWeb\TenantUi\TenantGlobalOperations;
use KarnoWeb\TenantUi\TenantImpactOperations;
use KarnoWeb\TenantUi\TenantPageBuilder;
use KarnoWeb\TenantUi\TenantRuntimeOperations;
use KarnoWeb\TenantUi\TenantUiTenantScope;

/**
 * @method static TenantUiTenantScope for(string $tenantKey)
 * @method static ThemeRegistry              themes()
 * @method static PresetRegistry             presets()
 * @method static VariantRegistry            variants()
 * @method static PageTypeRegistry           pageTypes()
 * @method static DataProviderRegistry       dataProviders()
 * @method static BladeRenderer              render()
 * @method static string                     version()
 *
 * @mixin \KarnoWeb\TenantUi\TenantUiManager
 *
 * @see \KarnoWeb\TenantUi\TenantUiManager
 */
class TenantUi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tenant-ui';
    }
}
