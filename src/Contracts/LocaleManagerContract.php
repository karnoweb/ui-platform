<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Contracts;

interface LocaleManagerContract
{
    public function current(): string;

    public function fallback(string $tenantKey): string;

    /**
     * @return list<string>
     */
    public function supported(string $tenantKey): array;
}
