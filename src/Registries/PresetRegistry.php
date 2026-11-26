<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Registries;

use InvalidArgumentException;
use KarnoWeb\TenantUi\Definitions\PresetDefinition;

final class PresetRegistry
{
    /** @var array<string, PresetDefinition> */
    private array $items = [];

    public function register(PresetDefinition $definition): void
    {
        if (isset($this->items[$definition->key])) {
            throw new InvalidArgumentException(
                "Preset [{$definition->key}] is already registered."
            );
        }

        $this->items[$definition->key] = $definition;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function get(string $key): PresetDefinition
    {
        return $this->items[$key]
            ?? throw new InvalidArgumentException("Preset [{$key}] is not registered.");
    }

    /** @return array<string, PresetDefinition> */
    public function all(): array
    {
        return $this->items;
    }
}
