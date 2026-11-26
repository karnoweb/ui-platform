<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Exceptions\PublishedVersionNotFoundException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\TenantUiVersion;
use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Models\UiPageRoute;
use KarnoWeb\TenantUi\Models\UiPageVersion;

final readonly class RuntimePayloadService
{
    public function __construct(
        private RuntimeCache $cache,
        private RouteCacheService $routeCache,
        private LocaleManagerContract $locales,
    ) {
    }

    public function byPageKey(
        string $tenantKey,
        string $pageKey,
        ?string $locale = null,
    ): array {
        $locale ??= $this->locales->current();
        $fallbackLocale = $this->locales->fallback($tenantKey);

        $globalVersionId = $this->globalVersionId($tenantKey);
        $pagePointer = $this->pagePointer($tenantKey, $pageKey);

        $pageId = (int) $pagePointer['page_id'];
        $pageVersionId = (int) $pagePointer['version_id'];

        $runtimeKey = $this->cache->key(
            'runtime',
            $tenantKey,
            (string) $globalVersionId,
            (string) $pageVersionId,
            $locale,
            $fallbackLocale,
        );

        return $this->cache->remember(
            $runtimeKey,
            function () use (
                $tenantKey,
                $locale,
                $fallbackLocale,
                $pageId,
                $globalVersionId,
                $pageVersionId
            ): array {
                $global = $this->globalPayload($tenantKey, $globalVersionId);
                $pagePayload = $this->pagePayload($pageId, $pageVersionId);

                return $this->localizeAndMerge(
                    tenantKey: $tenantKey,
                    locale: $locale,
                    fallbackLocale: $fallbackLocale,
                    globalVersionId: $globalVersionId,
                    pageVersionId: $pageVersionId,
                    global: $global,
                    pagePayload: $pagePayload,
                );
            },
            RuntimeCache::TTL_RUNTIME,
        );
    }

    public function bySlug(
        string $tenantKey,
        string $slug,
        ?string $locale = null,
    ): array {
        $locale ??= $this->locales->current();

        $route = $this->routeCache->remember(
            $tenantKey,
            $locale,
            $slug,
            function () use ($tenantKey, $locale, $slug): array {
                $row = UiPageRoute::query()
                    ->where('tenant_key', $tenantKey)
                    ->where('locale', $locale)
                    ->where('slug', $slug)
                    ->first(['page_id']);

                if (! $row) {
                    return [
                        'page_id' => 0,
                        'page_key' => '',
                    ];
                }

                $pageKey = (string) UiPage::query()
                    ->whereKey($row->page_id)
                    ->value('page_key');

                return [
                    'page_id' => (int) $row->page_id,
                    'page_key' => $pageKey,
                ];
            },
        );

        if ((int) ($route['page_id'] ?? 0) <= 0 || ($route['page_key'] ?? '') === '') {
            throw new PublishedVersionNotFoundException(
                "Published route [{$slug}] not found."
            );
        }

        return $this->byPageKey(
            $tenantKey,
            (string) $route['page_key'],
            $locale,
        );
    }

    public function etag(array $payload): string
    {
        return sprintf(
            'global-%s-page-%s-%s-%s',
            $payload['global_version'] ?? '0',
            $payload['page_version'] ?? '0',
            $payload['locale'] ?? 'default',
            $payload['fallback_locale'] ?? 'default',
        );
    }

    private function globalVersionId(string $tenantKey): int
    {
        $key = $this->cache->key('pointer', 'global', $tenantKey);

        $id = (int) $this->cache->remember(
            $key,
            fn (): int => (int) TenantUiConfig::query()
                ->where('tenant_key', $tenantKey)
                ->value('published_version_id'),
            RuntimeCache::TTL_POINTER,
        );

        if ($id <= 0) {
            throw new PublishedVersionNotFoundException(
                "Tenant [{$tenantKey}] has no published UI version."
            );
        }

        return $id;
    }

    private function pagePointer(
        string $tenantKey,
        string $pageKey,
    ): array {
        $key = $this->cache->key(
            'pointer',
            'page',
            $tenantKey,
            $pageKey,
        );

        $pointer = $this->cache->remember(
            $key,
            function () use ($tenantKey, $pageKey): array {
                $page = UiPage::query()
                    ->where('tenant_key', $tenantKey)
                    ->where('page_key', $pageKey)
                    ->whereNotNull('published_version_id')
                    ->first();

                if (! $page || ! $page->published_version_id) {
                    return [
                        'page_id' => 0,
                        'version_id' => 0,
                    ];
                }

                return [
                    'page_id' => $page->id,
                    'version_id' => (int) $page->published_version_id,
                ];
            },
            RuntimeCache::TTL_POINTER,
        );

        if ((int) ($pointer['page_id'] ?? 0) <= 0 || (int) ($pointer['version_id'] ?? 0) <= 0) {
            throw new PublishedVersionNotFoundException(
                "Page [{$pageKey}] has no published version."
            );
        }

        return $pointer;
    }

    private function globalPayload(
        string $tenantKey,
        int $versionId,
    ): array {
        return $this->cache->remember(
            $this->cache->key(
                'global',
                $tenantKey,
                (string) $versionId,
            ),
            fn (): array => (array) TenantUiVersion::query()
                ->findOrFail($versionId)
                ->payload,
            RuntimeCache::TTL_VERSION,
        );
    }

    private function pagePayload(
        int $pageId,
        int $versionId,
    ): array {
        return $this->cache->remember(
            $this->cache->key(
                'page-version',
                (string) $pageId,
                (string) $versionId,
            ),
            fn (): array => (array) UiPageVersion::query()
                ->findOrFail($versionId)
                ->payload,
            RuntimeCache::TTL_VERSION,
        );
    }

    private function localizeAndMerge(
        string $tenantKey,
        string $locale,
        string $fallbackLocale,
        int $globalVersionId,
        int $pageVersionId,
        array $global,
        array $pagePayload,
    ): array {
        $fallback = $fallbackLocale;

        $pageTranslations =
            $pagePayload['page']['translations'] ?? [];

        $pageTranslation =
            $pageTranslations[$locale]
            ?? $pageTranslations[$fallback]
            ?? [];

        $sections = [];

        foreach ($pagePayload['sections'] ?? [] as $section) {
            $translations = $section['translations'] ?? [];

            $content =
                $translations[$locale]
                ?? $translations[$fallback]
                ?? [];

            $sections[] = [
                'instance_key' => $section['instance_key'],
                'type' => $section['type'],
                'variant' => $section['variant'],
                'position' => $section['position'],
                'config' => $section['config'] ?? [],
                'content' => $content,
            ];
        }

        return [
            'tenant' => $tenantKey,
            'locale' => $locale,
            'fallback_locale' => $fallback,
            'global_version' => $globalVersionId,
            'page_version' => $pageVersionId,
            'theme' => $global['theme'],
            'preset' => $global['preset'] ?? null,
            'components' => $global['components'] ?? [],
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
