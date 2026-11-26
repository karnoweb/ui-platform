<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests;

use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;

final class ConfigurableTestLocaleManager implements LocaleManagerContract
{
    private string $current = 'fa';

    private string $fallback = 'fa';

    /** @var list<string> */
    private array $supported = ['fa', 'en', 'de'];

    public function setCurrent(string $locale): void
    {
        $this->current = $locale;
    }

    public function setFallback(string $locale): void
    {
        $this->fallback = $locale;
    }

    /**
     * @param  list<string>  $locales
     */
    public function setSupported(array $locales): void
    {
        $this->supported = $locales;
    }

    public function current(): string
    {
        return $this->current;
    }

    public function fallback(string $tenantKey): string
    {
        return $this->fallback;
    }

    public function supported(string $tenantKey): array
    {
        return $this->supported;
    }
}
