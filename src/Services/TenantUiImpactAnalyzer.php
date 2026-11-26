<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Exceptions\ModuleDisableImpactException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\TenantUiVersion;
use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Models\UiPageVersion;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final readonly class TenantUiImpactAnalyzer
{
    public function __construct(
        private VariantRegistry $variants,
        private PublishedGlobalConfigService $publishedGlobal,
    ) {
    }

    /**
     * @return array{
     *     global: list<array{component: string, variant: string}>,
     *     pages: list<array{page_key: string, section_type: string, variant: string}>
     * }
     */
    public function moduleUsages(string $tenantKey, string $moduleKey): array
    {
        return [
            'global' => $this->globalUsages($tenantKey, $moduleKey),
            'pages' => $this->pageUsages($tenantKey, $moduleKey),
        ];
    }

    public function canDisableModule(string $tenantKey, string $moduleKey): bool
    {
        $usages = $this->moduleUsages($tenantKey, $moduleKey);

        return $usages['global'] === [] && $usages['pages'] === [];
    }

    public function assertCanDisableModule(string $tenantKey, string $moduleKey): void
    {
        $usages = $this->moduleUsages($tenantKey, $moduleKey);

        if ($usages['global'] === [] && $usages['pages'] === []) {
            return;
        }

        throw new ModuleDisableImpactException(
            "Cannot disable module [{$moduleKey}]; published UI depends on it.",
            $usages,
        );
    }

    /**
     * @return list<array{page_key: string, section_type: string, variant: string}>
     */
    public function publishedUsagesOfModule(string $tenantKey, string $moduleKey): array
    {
        return $this->moduleUsages($tenantKey, $moduleKey)['pages'];
    }

    /**
     * @return list<array{component: string, variant: string}>
     */
    private function globalUsages(string $tenantKey, string $moduleKey): array
    {
        $config = TenantUiConfig::query()
            ->where('tenant_key', $tenantKey)
            ->first();

        if (! $config?->published_version_id) {
            return [];
        }

        /** @var TenantUiVersion|null $version */
        $version = TenantUiVersion::query()->find($config->published_version_id);

        if (! $version) {
            return [];
        }

        $usages = [];

        foreach ($version->payload['components'] ?? [] as $component => $variantKey) {
            if (! is_string($variantKey) || ! $this->variants->has($variantKey)) {
                continue;
            }

            $variant = $this->variants->get($variantKey);

            if (in_array($moduleKey, $variant->requiresModules, true)) {
                $usages[] = [
                    'component' => (string) $component,
                    'variant' => $variantKey,
                ];
            }
        }

        return $usages;
    }

    /**
     * @return list<array{page_key: string, section_type: string, variant: string}>
     */
    private function pageUsages(string $tenantKey, string $moduleKey): array
    {
        $usages = [];

        $pages = UiPage::query()
            ->where('tenant_key', $tenantKey)
            ->whereNotNull('published_version_id')
            ->get();

        foreach ($pages as $page) {
            /** @var UiPageVersion|null $version */
            $version = UiPageVersion::query()->find($page->published_version_id);

            if (! $version) {
                continue;
            }

            foreach ($version->payload['sections'] ?? [] as $section) {
                $variantKey = (string) ($section['variant'] ?? '');

                if ($variantKey === '' || ! $this->variants->has($variantKey)) {
                    continue;
                }

                $variant = $this->variants->get($variantKey);

                if (in_array($moduleKey, $variant->requiresModules, true)) {
                    $usages[] = [
                        'page_key' => $page->page_key,
                        'section_type' => (string) $section['type'],
                        'variant' => $variantKey,
                    ];
                }
            }
        }

        return $usages;
    }
}
