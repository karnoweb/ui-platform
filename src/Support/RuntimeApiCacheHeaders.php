<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Support;

final class RuntimeApiCacheHeaders
{
    public static function cacheControl(): string
    {
        $maxAge = (int) config('tenant-ui.runtime_api.cache_max_age', 300);
        $visibility = (string) config(
            'tenant-ui.runtime_api.cache_visibility',
            'private',
        );

        if (! in_array($visibility, ['private', 'public'], true)) {
            $visibility = 'private';
        }

        return $visibility.', max-age='.$maxAge;
    }
}
