<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi;

use KarnoWeb\TenantUi\Services\TenantUiImpactAnalyzer;

final readonly class TenantImpactOperations
{
    public function __construct(
        private string $tenantKey,
        private TenantUiImpactAnalyzer $analyzer,
    ) {
    }

    /**
     * @return array{
     *     global: list<array{component: string, variant: string}>,
     *     pages: list<array{page_key: string, section_type: string, variant: string}>
     * }
     */
    public function moduleUsages(string $moduleKey): array
    {
        return $this->analyzer->moduleUsages($this->tenantKey, $moduleKey);
    }

    public function canDisableModule(string $moduleKey): bool
    {
        return $this->analyzer->canDisableModule($this->tenantKey, $moduleKey);
    }

    public function assertCanDisableModule(string $moduleKey): void
    {
        $this->analyzer->assertCanDisableModule($this->tenantKey, $moduleKey);
    }
}
