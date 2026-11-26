<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi;

use KarnoWeb\TenantUi\Models\TenantUiVersion;
use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Models\UiPageSection;
use KarnoWeb\TenantUi\Models\UiPageSectionTranslation;
use KarnoWeb\TenantUi\Models\UiPageVersion;
use KarnoWeb\TenantUi\Services\DefinitionCatalogService;
use KarnoWeb\TenantUi\Services\PageDraftService;
use KarnoWeb\TenantUi\Services\PagePublisher;
use KarnoWeb\TenantUi\Services\PreviewPayloadService;
use KarnoWeb\TenantUi\Services\RuntimePayloadService;
use KarnoWeb\TenantUi\Services\TenantUiDraftService;
use KarnoWeb\TenantUi\Services\TenantUiImpactAnalyzer;
use KarnoWeb\TenantUi\Services\TenantUiPublisher;

final readonly class TenantPageBuilder
{
    public function __construct(
        private string $tenantKey,
        private PageDraftService $pages,
        private PagePublisher $publisher,
    ) {
    }

    public function createPage(
        string $pageKey,
        string $type,
        bool $isActive = true,
    ): UiPage {
        return $this->pages->createPage(
            $this->tenantKey,
            $pageKey,
            $type,
            $isActive,
        );
    }

    public function setActive(
        int $pageId,
        bool $isActive,
        int $expectedRevision,
    ): UiPage {
        return $this->pages->setActive(
            $this->tenantKey,
            $pageId,
            $isActive,
            $expectedRevision,
        );
    }

    public function upsertPageTranslation(
        int $pageId,
        string $locale,
        array $data,
        int $expectedRevision,
    ): UiPage {
        return $this->pages->upsertPageTranslation(
            $this->tenantKey,
            $pageId,
            $locale,
            $data,
            $expectedRevision,
        );
    }

    public function addSection(
        int $pageId,
        string $type,
        string $variantKey,
        array $config,
        int $expectedRevision,
    ): UiPageSection {
        return $this->pages->addSection(
            $this->tenantKey,
            $pageId,
            $type,
            $variantKey,
            $config,
            $expectedRevision,
        );
    }

    public function updateSection(
        int $sectionId,
        string $variantKey,
        array $config,
        bool $isEnabled,
        int $expectedRevision,
    ): UiPageSection {
        return $this->pages->updateSection(
            $this->tenantKey,
            $sectionId,
            $variantKey,
            $config,
            $isEnabled,
            $expectedRevision,
        );
    }

    public function upsertSectionTranslation(
        int $sectionId,
        string $locale,
        array $content,
        int $expectedRevision,
    ): UiPageSectionTranslation {
        return $this->pages->upsertSectionTranslation(
            $this->tenantKey,
            $sectionId,
            $locale,
            $content,
            $expectedRevision,
        );
    }

    public function reorder(
        int $pageId,
        array $instanceKeys,
        int $expectedRevision,
    ): UiPage {
        return $this->pages->reorder(
            $this->tenantKey,
            $pageId,
            $instanceKeys,
            $expectedRevision,
        );
    }

    public function deleteSection(
        int $sectionId,
        int $expectedRevision,
    ): void {
        $this->pages->deleteSection(
            $this->tenantKey,
            $sectionId,
            $expectedRevision,
        );
    }

    public function publish(
        int $pageId,
        ?string $publishedBy = null,
    ): ?UiPageVersion {
        return $this->publisher->publish(
            $this->tenantKey,
            $pageId,
            $publishedBy,
        );
    }
}
