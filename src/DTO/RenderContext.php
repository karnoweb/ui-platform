<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\DTO;

final readonly class RenderContext
{
    public function __construct(
        public string $tenantKey,
        public string $locale,
        public ?string $pageKey = null,
        public ?string $pageType = null,
        public ?string $sectionInstanceKey = null,
        public array $extra = [],
    ) {
    }
}
