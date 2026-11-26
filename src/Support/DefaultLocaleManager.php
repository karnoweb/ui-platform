<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Support;

use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;

final class DefaultLocaleManager implements LocaleManagerContract
{
    public function current(): string
    {
        return app()->getLocale();
    }

    public function fallback(string $tenantKey): string
    {
        return (string) config('app.fallback_locale', 'en');
    }

    public function supported(string $tenantKey): array
    {
        $fallback = $this->fallback($tenantKey);
        $current = (string) config('app.locale', $fallback);

        return array_values(array_unique([$current, $fallback]));
    }
}
