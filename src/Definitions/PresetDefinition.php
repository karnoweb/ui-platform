<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Definitions;

final readonly class PresetDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public array $components,
        public array $supportedThemes = [],
        public array $meta = [],
    ) {
    }
}
