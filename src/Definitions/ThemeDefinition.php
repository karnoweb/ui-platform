<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Definitions;

final readonly class ThemeDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public string $layout,
        public array $capabilities = [],
        public ?string $bladeLayout = null,
        public array $meta = [],
    ) {
    }
}
