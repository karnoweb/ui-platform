<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Registries;

use InvalidArgumentException;
use KarnoWeb\TenantUi\Definitions\VariantDefinition;

final class VariantRegistry
{
    /** @var array<string, VariantDefinition> */
    private array $items = [];

    public function register(VariantDefinition $definition): void
    {
        if (isset($this->items[$definition->key])) {
            throw new InvalidArgumentException(
                "Variant [{$definition->key}] is already registered."
            );
        }

        $this->items[$definition->key] = $definition;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function get(string $key): VariantDefinition
    {
        return $this->items[$key]
            ?? throw new InvalidArgumentException("Variant [{$key}] is not registered.");
    }

    /** @return array<string, VariantDefinition> */
    public function all(): array
    {
        return $this->items;
    }

    /** @return array<string, VariantDefinition> */
    public function byType(string $type, ?string $scope = null): array
    {
        return array_filter(
            $this->items,
            static fn (VariantDefinition $item): bool =>
                $item->type === $type
                && ($scope === null || $item->scope === $scope)
        );
    }
}
