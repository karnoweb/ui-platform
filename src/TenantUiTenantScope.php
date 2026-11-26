<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi;

use Illuminate\Contracts\Container\Container;
use KarnoWeb\TenantUi\Services\DefinitionCatalogService;
use KarnoWeb\TenantUi\Services\PageDraftService;
use KarnoWeb\TenantUi\Services\PagePublisher;
use KarnoWeb\TenantUi\Services\PreviewPayloadService;
use KarnoWeb\TenantUi\Services\RuntimePayloadService;
use KarnoWeb\TenantUi\Services\TenantUiDraftService;
use KarnoWeb\TenantUi\Services\TenantUiImpactAnalyzer;
use KarnoWeb\TenantUi\Services\TenantUiPublisher;

final readonly class TenantUiTenantScope
{
    public function __construct(
        private string $tenantKey,
        private Container $container,
    ) {
    }

    public function key(): string
    {
        return $this->tenantKey;
    }

    public function global(): TenantGlobalOperations
    {
        return new TenantGlobalOperations(
            $this->tenantKey,
            $this->container->make(TenantUiDraftService::class),
            $this->container->make(TenantUiPublisher::class),
        );
    }

    public function pages(): TenantPageBuilder
    {
        return new TenantPageBuilder(
            $this->tenantKey,
            $this->container->make(PageDraftService::class),
            $this->container->make(PagePublisher::class),
        );
    }

    public function runtime(): TenantRuntimeOperations
    {
        return new TenantRuntimeOperations(
            $this->tenantKey,
            $this->container->make(RuntimePayloadService::class),
        );
    }

    public function impact(): TenantImpactOperations
    {
        return new TenantImpactOperations(
            $this->tenantKey,
            $this->container->make(TenantUiImpactAnalyzer::class),
        );
    }

    public function preview(int $pageId, ?string $locale = null): array
    {
        return $this->container
            ->make(PreviewPayloadService::class)
            ->make($this->tenantKey, $pageId, $locale);
    }

    public function catalog(): DefinitionCatalogService
    {
        return $this->container->make(DefinitionCatalogService::class);
    }
}
