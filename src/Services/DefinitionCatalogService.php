<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Exceptions\CompatibilityException;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final readonly class DefinitionCatalogService
{
    public function __construct(
        private ThemeRegistry $themes,
        private PresetRegistry $presets,
        private VariantRegistry $variants,
        private PageTypeRegistry $pageTypes,
        private CompatibilityService $compatibility,
    ) {
    }

    public function all(): array
    {
        return [
            'themes' => array_values(array_map(
                fn ($theme) => [
                    'key' => $theme->key,
                    'label' => $theme->label,
                    'layout' => $theme->layout,
                    'capabilities' => $theme->capabilities,
                    'meta' => $theme->meta,
                ],
                $this->themes->all(),
            )),

            'presets' => array_values(array_map(
                fn ($preset) => [
                    'key' => $preset->key,
                    'label' => $preset->label,
                    'supported_themes' => $preset->supportedThemes,
                    'components' => $preset->components,
                    'meta' => $preset->meta,
                ],
                $this->presets->all(),
            )),

            'page_types' => array_values(array_map(
                fn ($type) => [
                    'key' => $type->key,
                    'label' => $type->label,
                    'section_rules' => $type->sectionRules,
                    'meta' => $type->meta,
                ],
                $this->pageTypes->all(),
            )),
        ];
    }

    public function variantsFor(
        string $tenantKey,
        string $themeKey,
        string $scope,
        ?string $type = null,
        ?string $pageType = null,
    ): array {
        $items = [];

        foreach ($this->variants->all() as $variant) {
            if ($variant->scope !== $scope) {
                continue;
            }

            if ($type !== null && $variant->type !== $type) {
                continue;
            }

            if (! $this->compatibility->isSelectable($variant)) {
                continue;
            }

            try {
                $this->compatibility->assertVariant(
                    tenantKey: $tenantKey,
                    themeKey: $themeKey,
                    variantKey: $variant->key,
                    expectedScope: $scope,
                    expectedType: $variant->type,
                    pageType: $pageType,
                );
            } catch (CompatibilityException) {
                continue;
            }

            $items[] = [
                'key' => $variant->key,
                'type' => $variant->type,
                'scope' => $variant->scope,
                'label' => $variant->label,
                'config_fields' => $variant->configFields,
                'translation_fields' => $variant->translationFields,
                'optional_modules' => $variant->optionalModules,
                'status' => $variant->status,
                'meta' => $variant->meta,
            ];
        }

        return $items;
    }
}
