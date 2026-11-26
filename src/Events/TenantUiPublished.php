<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Events;

final readonly class TenantUiPublished
{
    public function __construct(
        public string $tenantKey,
        public int $versionId,
        public ?string $publishedBy,
    ) {
    }
}
