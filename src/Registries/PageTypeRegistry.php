<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Registries;

use InvalidArgumentException;
use KarnoWeb\TenantUi\Definitions\PageTypeDefinition;

final class PageTypeRegistry
{
    /** @var array<string, PageTypeDefinition> */
    private array $items = [];

    public function register(PageTypeDefinition $definition): void
    {
        if (isset($this->items[$definition->key])) {
            throw new InvalidArgumentException(
                "Page type [{$definition->key}] is already registered."
            );
        }

        $this->items[$definition->key] = $definition;
    }

    public function get(string $key): PageTypeDefinition
    {
        return $this->items[$key]
            ?? throw new InvalidArgumentException("Page type [{$key}] is not registered.");
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    /** @return array<string, PageTypeDefinition> */
    public function all(): array
    {
        return $this->items;
    }
}
