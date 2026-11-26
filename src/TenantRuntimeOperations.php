<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi;

use KarnoWeb\TenantUi\Services\RuntimePayloadService;

final readonly class TenantRuntimeOperations
{
    public function __construct(
        private string $tenantKey,
        private RuntimePayloadService $runtime,
    ) {
    }

    public function byPageKey(string $pageKey, ?string $locale = null): array
    {
        return $this->runtime->byPageKey(
            $this->tenantKey,
            $pageKey,
            $locale,
        );
    }

    public function bySlug(string $slug, ?string $locale = null): array
    {
        return $this->runtime->bySlug(
            $this->tenantKey,
            $slug,
            $locale,
        );
    }

    public function etag(array $payload): string
    {
        return $this->runtime->etag($payload);
    }
}
