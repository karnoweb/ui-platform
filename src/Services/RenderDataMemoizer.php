<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

/**
 * Request-scoped memoization for section/global data providers.
 */
final class RenderDataMemoizer
{
    /** @var array<string, array> */
    private array $store = [];

    public function remember(string $key, callable $loader): array
    {
        if (array_key_exists($key, $this->store)) {
            return $this->store[$key];
        }

        return $this->store[$key] = (array) $loader();
    }

    public function forgetAll(): void
    {
        $this->store = [];
    }
}
