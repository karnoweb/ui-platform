<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Exceptions\CompatibilityException;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;
use KarnoWeb\TenantUi\Support\ModuleResolverGate;

final readonly class CompatibilityService
{
    public function __construct(
        private ThemeRegistry $themes,
        private VariantRegistry $variants,
        private ModuleResolverGate $modules,
    ) {
    }

    public function assertVariant(
        string $tenantKey,
        string $themeKey,
        string $variantKey,
        string $expectedScope,
        string $expectedType,
        ?string $pageType = null,
    ): VariantDefinition {
        $theme = $this->themes->get($themeKey);
        $variant = $this->variants->get($variantKey);

        if ($variant->status === VariantDefinition::STATUS_DISABLED) {
            throw new CompatibilityException(
                "Variant [{$variantKey}] is disabled."
            );
        }

        if ($variant->scope !== $expectedScope) {
            throw new CompatibilityException(
                "Variant [{$variantKey}] must have scope [{$expectedScope}]."
            );
        }

        if ($variant->type !== $expectedType) {
            throw new CompatibilityException(
                "Variant [{$variantKey}] belongs to type [{$variant->type}], not [{$expectedType}]."
            );
        }

        if (
            $pageType !== null
            && $variant->allowedPageTypes !== []
            && ! in_array($pageType, $variant->allowedPageTypes, true)
        ) {
            throw new CompatibilityException(
                "Variant [{$variantKey}] is not allowed on page type [{$pageType}]."
            );
        }

        foreach ($variant->requiresModules as $module) {
            if (! $this->modules->enabled($tenantKey, $module)) {
                throw new CompatibilityException(
                    "Variant [{$variantKey}] requires module [{$module}]."
                );
            }
        }

        if (
            $variant->supportedLayouts !== []
            && ! in_array($theme->layout, $variant->supportedLayouts, true)
        ) {
            throw new CompatibilityException(
                "Variant [{$variantKey}] does not support layout [{$theme->layout}]."
            );
        }

        foreach ($variant->requiredThemeCapabilities as $capability) {
            if (! in_array($capability, $theme->capabilities, true)) {
                throw new CompatibilityException(
                    "Variant [{$variantKey}] requires theme capability [{$capability}]."
                );
            }
        }

        return $variant;
    }

    public function isSelectable(VariantDefinition $variant): bool
    {
        return $variant->status === VariantDefinition::STATUS_ACTIVE;
    }
}
