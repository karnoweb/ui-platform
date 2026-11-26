<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Definitions;

final readonly class PageTypeDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public array $sectionRules,
        public array $meta = [],
    ) {
    }

    public function allows(string $sectionType): bool
    {
        return array_key_exists($sectionType, $this->sectionRules);
    }

    public function rule(string $sectionType): ?array
    {
        return $this->sectionRules[$sectionType] ?? null;
    }
}
