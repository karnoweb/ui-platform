<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi;

use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\TenantUiVersion;
use KarnoWeb\TenantUi\Services\TenantUiDraftService;
use KarnoWeb\TenantUi\Services\TenantUiPublisher;

final readonly class TenantGlobalDraft
{
    public function __construct(
        private string $tenantKey,
        private TenantUiDraftService $drafts,
    ) {
    }

    public function save(
        string $themeKey,
        ?string $presetKey,
        array $overrides,
        ?int $expectedRevision = null,
    ): TenantUiConfig {
        return $this->drafts->save(
            tenantKey: $this->tenantKey,
            themeKey: $themeKey,
            presetKey: $presetKey,
            overrides: $overrides,
            expectedRevision: $expectedRevision,
        );
    }
}

final readonly class TenantGlobalOperations
{
    public function __construct(
        private string $tenantKey,
        private TenantUiDraftService $drafts,
        private TenantUiPublisher $publisher,
    ) {
    }

    public function draft(): TenantGlobalDraft
    {
        return new TenantGlobalDraft($this->tenantKey, $this->drafts);
    }

    public function publish(?string $publishedBy = null): TenantUiVersion
    {
        return $this->publisher->publish($this->tenantKey, $publishedBy);
    }
}
