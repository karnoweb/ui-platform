<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\UiPage;

final readonly class PreviewPayloadService
{
    public function __construct(
        private TenantUiResolver $globalResolver,
        private PageDraftValidator $pageValidator,
        private PageCompiler $pageCompiler,
        private LocaleManagerContract $locales,
    ) {
    }

    public function make(
        string $tenantKey,
        int $pageId,
        ?string $locale = null,
    ): array {
        $locale ??= $this->locales->current();

        $globalConfig = TenantUiConfig::query()
            ->where('tenant_key', $tenantKey)
            ->firstOrFail();

        $page = UiPage::query()
            ->where('tenant_key', $tenantKey)
            ->whereKey($pageId)
            ->firstOrFail();

        $page->load([
            'translations',
            'sections.translations',
        ]);

        $this->pageValidator->validate($page);

        $global = $this->globalResolver->resolve($globalConfig);
        $pagePayload = $this->pageCompiler->compile($page);

        $fallback = $this->locales->fallback($tenantKey);

        $pageTranslation =
            $pagePayload['page']['translations'][$locale]
            ?? $pagePayload['page']['translations'][$fallback]
            ?? [];

        $sections = [];

        foreach ($pagePayload['sections'] as $section) {
            $content =
                $section['translations'][$locale]
                ?? $section['translations'][$fallback]
                ?? [];

            $sections[] = [
                'instance_key' => $section['instance_key'],
                'type' => $section['type'],
                'variant' => $section['variant'],
                'position' => $section['position'],
                'config' => $section['config'],
                'content' => $content,
            ];
        }

        return [
            'preview' => true,
            'tenant' => $tenantKey,
            'locale' => $locale,
            'theme' => $global['theme'],
            'preset' => $global['preset'],
            'components' => $global['components'],
            'page' => [
                'key' => $pagePayload['page']['key'],
                'type' => $pagePayload['page']['type'],
                'title' => $pageTranslation['title'] ?? null,
                'slug' => $pageTranslation['slug'] ?? null,
                'seo_title' => $pageTranslation['seo_title'] ?? null,
                'seo_description' => $pageTranslation['seo_description'] ?? null,
                'extra' => $pageTranslation['extra'] ?? [],
            ],
            'sections' => $sections,
        ];
    }
}
