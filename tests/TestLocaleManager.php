<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests;

use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;

final class TestLocaleManager implements LocaleManagerContract
{
    public function current(): string
    {
        return 'fa';
    }

    public function fallback(string $tenantKey): string
    {
        return 'fa';
    }

    public function supported(string $tenantKey): array
    {
        return ['fa', 'en'];
    }
}
