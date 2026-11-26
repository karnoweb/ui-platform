<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Registries;

use InvalidArgumentException;
use KarnoWeb\TenantUi\Definitions\ThemeDefinition;

final class ThemeRegistry
{
    /** @var array<string, ThemeDefinition> */
    private array $items = [];

    public function register(ThemeDefinition $definition): void
    {
        if (isset($this->items[$definition->key])) {
            throw new InvalidArgumentException(
                "Theme [{$definition->key}] is already registered."
            );
        }

        $this->items[$definition->key] = $definition;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function get(string $key): ThemeDefinition
    {
        return $this->items[$key]
            ?? throw new InvalidArgumentException("Theme [{$key}] is not registered.");
    }

    /** @return array<string, ThemeDefinition> */
    public function all(): array
    {
        return $this->items;
    }
}
