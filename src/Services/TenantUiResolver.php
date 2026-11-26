<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Exceptions\TenantUiException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;

final readonly class TenantUiResolver
{
    public function __construct(
        private ThemeRegistry $themes,
        private PresetRegistry $presets,
        private CompatibilityService $compatibility,
    ) {
    }

    public function resolve(TenantUiConfig $config): array
    {
        $theme = $this->themes->get($config->theme_key);

        $presetComponents = [];

        if ($config->preset_key !== null) {
            $preset = $this->presets->get($config->preset_key);

            if (
                $preset->supportedThemes !== []
                && ! in_array($theme->key, $preset->supportedThemes, true)
            ) {
                throw new TenantUiException(
                    "Preset [{$preset->key}] does not support theme [{$theme->key}]."
                );
            }

            $presetComponents = $preset->components;
        }

        $defaults = array_filter(
            (array) config('tenant-ui.defaults.components', []),
            static fn (mixed $value): bool => $value !== null
        );

        $overrides = (array) ($config->overrides ?? []);

        $componentTypes = array_unique([
            ...array_keys($defaults),
            ...array_keys($presetComponents),
            ...array_keys($overrides),
        ]);

        $resolved = [];

        foreach ($componentTypes as $type) {
            $variantKey =
                $overrides[$type]
                ?? $presetComponents[$type]
                ?? $defaults[$type]
                ?? null;

            if ($variantKey === null) {
                continue;
            }

            $this->compatibility->assertVariant(
                tenantKey: $config->tenant_key,
                themeKey: $theme->key,
                variantKey: $variantKey,
                expectedScope: VariantDefinition::SCOPE_GLOBAL,
                expectedType: $type,
            );

            $resolved[$type] = $variantKey;
        }

        ksort($resolved);

        return [
            'theme' => $theme->key,
            'preset' => $config->preset_key,
            'components' => $resolved,
        ];
    }
}
