<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Registries;

use InvalidArgumentException;

final class DataProviderRegistry
{
    /**
     * @var array<string, class-string>
     */
    private array $providers = [];

    public function register(string $scope, string $type, string $providerClass): void
    {
        $key = "{$scope}:{$type}";

        if (isset($this->providers[$key])) {
            throw new InvalidArgumentException(
                "Data provider [{$key}] is already registered."
            );
        }

        $this->providers[$key] = $providerClass;
    }

    public function get(string $scope, string $type): ?string
    {
        return $this->providers["{$scope}:{$type}"] ?? null;
    }

    /** @return array<string, class-string> */
    public function all(): array
    {
        return $this->providers;
    }
}
