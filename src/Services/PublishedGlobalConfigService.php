<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Exceptions\PublishedVersionNotFoundException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\TenantUiVersion;

final readonly class PublishedGlobalConfigService
{
    public function themeKey(string $tenantKey): string
    {
        return (string) $this->payload($tenantKey)['theme'];
    }

    public function payload(string $tenantKey): array
    {
        /** @var TenantUiConfig $config */
        $config = TenantUiConfig::query()
            ->where('tenant_key', $tenantKey)
            ->firstOrFail();

        if (! $config->published_version_id) {
            throw new PublishedVersionNotFoundException(
                "Tenant [{$tenantKey}] has no published global UI version."
            );
        }

        /** @var TenantUiVersion $version */
        $version = TenantUiVersion::query()->findOrFail($config->published_version_id);

        return (array) $version->payload;
    }
}
