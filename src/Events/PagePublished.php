<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Events;

final readonly class PagePublished
{
    public function __construct(
        public string $tenantKey,
        public int $pageId,
        public int $versionId,
        public ?string $publishedBy,
    ) {
    }
}
