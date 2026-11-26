<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;
use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Registries\DataProviderRegistry;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final class DoctorCommand extends Command
{
    protected $signature = 'tenant-ui:doctor';

    protected $description = 'Validate tenant UI registry configuration.';

    public function handle(
        ThemeRegistry $themes,
        PresetRegistry $presets,
        VariantRegistry $variants,
        PageTypeRegistry $pageTypes,
        DataProviderRegistry $providers,
    ): int {
        $errors = [];

        foreach ((array) config('tenant-ui.defaults.components', []) as $type => $variantKey) {
            if ($variantKey === null) {
                continue;
            }

            if (! $variants->has($variantKey)) {
                $errors[] = "Default [{$type}] references missing variant [{$variantKey}].";
            }
        }

        foreach ($presets->all() as $preset) {
            foreach ($preset->supportedThemes as $themeKey) {
                if (! $themes->has($themeKey)) {
                    $errors[] =
                        "Preset [{$preset->key}] references missing theme [{$themeKey}].";
                }
            }

            foreach ($preset->components as $type => $variantKey) {
                if (! $variants->has($variantKey)) {
                    $errors[] =
                        "Preset [{$preset->key}] references missing variant [{$variantKey}].";

                    continue;
                }

                $variant = $variants->get($variantKey);

                if ($variant->scope !== VariantDefinition::SCOPE_GLOBAL) {
                    $errors[] =
                        "Preset [{$preset->key}] variant [{$variantKey}] is not global.";
                }

                if ($variant->type !== $type) {
                    $errors[] =
                        "Preset [{$preset->key}] maps [{$type}] to incompatible variant [{$variantKey}].";
                }
            }
        }

        foreach ($variants->all() as $variant) {
            if (
                $variant->bladeView !== null
                && ! View::exists($variant->bladeView)
            ) {
                $errors[] =
                    "Variant [{$variant->key}] references missing view [{$variant->bladeView}].";
            }
        }

        foreach ($pageTypes->all() as $pageType) {
            foreach (array_keys($pageType->sectionRules) as $sectionType) {
                $hasVariant = false;

                foreach ($variants->byType(
                    $sectionType,
                    VariantDefinition::SCOPE_SECTION
                ) as $variant) {
                    $hasVariant = true;
                    break;
                }

                if (! $hasVariant) {
                    $this->warn(
                        "Page type [{$pageType->key}] allows [{$sectionType}] but no section variant is registered."
                    );
                }
            }
        }

        foreach ($providers->all() as $key => $providerClass) {
            if (! class_exists($providerClass)) {
                $errors[] = "Data provider [{$key}] class [{$providerClass}] does not exist.";
            }
        }

        if ($errors === []) {
            $this->info('Tenant UI registry is healthy.');

            return self::SUCCESS;
        }

        foreach ($errors as $error) {
            $this->error($error);
        }

        return self::FAILURE;
    }
}
