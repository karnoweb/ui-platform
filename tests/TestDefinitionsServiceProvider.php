<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests;

use Illuminate\Support\ServiceProvider;
use KarnoWeb\TenantUi\Definitions\PageTypeDefinition;
use KarnoWeb\TenantUi\Definitions\PresetDefinition;
use KarnoWeb\TenantUi\Definitions\ThemeDefinition;
use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final class TestDefinitionsServiceProvider extends ServiceProvider
{
    public function boot(
        ThemeRegistry $themes,
        PresetRegistry $presets,
        VariantRegistry $variants,
        PageTypeRegistry $pageTypes,
    ): void {
        $themes->register(new ThemeDefinition(
            key: 'modern',
            label: 'Modern',
            layout: 'full-width',
            capabilities: ['sticky-header', 'wide-container'],
            bladeLayout: 'website.themes.modern.master',
        ));

        $themes->register(new ThemeDefinition(
            key: 'boxed',
            label: 'Boxed',
            layout: 'boxed',
            capabilities: [],
            bladeLayout: 'website.themes.boxed.master',
        ));

        foreach ([
            ['header-default', 'header', VariantDefinition::SCOPE_GLOBAL, []],
            ['header-1', 'header', VariantDefinition::SCOPE_GLOBAL, []],
            [
                'header-5',
                'header',
                VariantDefinition::SCOPE_GLOBAL,
                ['requiresModules' => ['search'], 'supportedLayouts' => ['full-width']],
            ],
            ['footer-default', 'footer', VariantDefinition::SCOPE_GLOBAL, []],
            ['footer-2', 'footer', VariantDefinition::SCOPE_GLOBAL, []],
            ['hero-2', 'hero', VariantDefinition::SCOPE_SECTION, ['supportedLayouts' => ['full-width']]],
            [
                'products-3',
                'products',
                VariantDefinition::SCOPE_SECTION,
                [
                    'configFields' => [
                        [
                            'key' => 'limit',
                            'type' => 'number',
                            'rules' => ['nullable', 'integer', 'min:1', 'max:50'],
                            'default' => 12,
                        ],
                    ],
                    'translationFields' => [
                        [
                            'key' => 'title',
                            'type' => 'text',
                            'rules' => ['nullable', 'string', 'max:255'],
                        ],
                    ],
                ],
            ],
            [
                'story-2',
                'story',
                VariantDefinition::SCOPE_SECTION,
                [
                    'requiresModules' => ['stories'],
                    'allowedPageTypes' => ['home'],
                    'configFields' => [
                        [
                            'key' => 'limit',
                            'type' => 'number',
                            'rules' => ['nullable', 'integer', 'min:1', 'max:30'],
                            'default' => 10,
                        ],
                    ],
                    'translationFields' => [
                        [
                            'key' => 'title',
                            'type' => 'text',
                            'rules' => ['nullable', 'string', 'max:255'],
                        ],
                    ],
                ],
            ],
            [
                'banner-boxed',
                'banner',
                VariantDefinition::SCOPE_SECTION,
                ['supportedLayouts' => ['boxed']],
            ],
        ] as [$key, $type, $scope, $extra]) {
            $variants->register(new VariantDefinition(
                key: $key,
                type: $type,
                scope: $scope,
                label: $key,
                bladeView: null,
                requiresModules: $extra['requiresModules'] ?? [],
                supportedLayouts: $extra['supportedLayouts'] ?? [],
                allowedPageTypes: $extra['allowedPageTypes'] ?? [],
                configFields: $extra['configFields'] ?? [],
                translationFields: $extra['translationFields'] ?? [],
                status: $extra['status'] ?? VariantDefinition::STATUS_ACTIVE,
            ));
        }

        $variants->register(new VariantDefinition(
            key: 'header-deprecated',
            type: 'header',
            scope: VariantDefinition::SCOPE_GLOBAL,
            label: 'Deprecated Header',
            status: VariantDefinition::STATUS_DEPRECATED,
        ));

        $presets->register(new PresetDefinition(
            key: 'modern-shop',
            label: 'Modern Shop',
            supportedThemes: ['modern'],
            components: [
                'header' => 'header-5',
                'footer' => 'footer-2',
            ],
        ));

        $pageTypes->register(new PageTypeDefinition(
            key: 'home',
            label: 'Home',
            sectionRules: [
                'hero' => ['min' => 0, 'max' => 1],
                'story' => ['min' => 0, 'max' => 1],
                'products' => ['min' => 0, 'max' => null],
                'banner' => ['min' => 0, 'max' => null],
            ],
        ));
    }
}
