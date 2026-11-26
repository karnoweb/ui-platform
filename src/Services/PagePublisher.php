<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Illuminate\Support\Facades\DB;
use KarnoWeb\TenantUi\DTO\UiValidationContext;
use KarnoWeb\TenantUi\Events\PagePublished;
use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Models\UiPageRoute;
use KarnoWeb\TenantUi\Models\UiPageVersion;
use KarnoWeb\TenantUi\Support\VersionChecksum;

final readonly class PagePublisher
{
    public function __construct(
        private PageDraftValidator $validator,
        private PageCompiler $compiler,
        private RuntimeCache $cache,
        private RouteCacheService $routeCache,
    ) {
    }

    public function publish(
        string $tenantKey,
        int $pageId,
        ?string $publishedBy = null,
    ): ?UiPageVersion {
        [$version, $page, $wentOffline, $oldRoutes, $warmRoutes] = DB::transaction(function () use (
            $tenantKey,
            $pageId,
            $publishedBy,
        ) {
            /** @var UiPage $page */
            $page = UiPage::query()
                ->where('tenant_key', $tenantKey)
                ->whereKey($pageId)
                ->lockForUpdate()
                ->firstOrFail();

            $oldRoutes = UiPageRoute::query()
                ->where('page_id', $page->id)
                ->get(['tenant_key', 'locale', 'slug'])
                ->map(static fn ($route): array => [
                    'tenant_key' => (string) $route->tenant_key,
                    'locale' => (string) $route->locale,
                    'slug' => (string) $route->slug,
                ])
                ->all();

            if (! $page->is_active) {
                UiPageRoute::query()
                    ->where('page_id', $page->id)
                    ->delete();

                $page->forceFill([
                    'published_version_id' => null,
                ])->save();

                return [null, $page, true, $oldRoutes, []];
            }

            $page->load([
                'translations',
                'sections.translations',
            ]);

            $this->validator->validate($page, UiValidationContext::Publish);

            $payload = $this->compiler->compile($page);
            $checksum = VersionChecksum::hash($payload);

            if ($page->published_version_id) {
                /** @var UiPageVersion|null $current */
                $current = UiPageVersion::query()->find($page->published_version_id);

                if ($current && $current->checksum === $checksum) {
                    $warmRoutes = $this->syncRoutesInDatabase($page, $current);

                    return [$current, $page, false, $oldRoutes, $warmRoutes];
                }
            }

            $nextVersion = ((int) UiPageVersion::query()
                ->where('page_id', $page->id)
                ->max('version')) + 1;

            /** @var UiPageVersion $version */
            $version = UiPageVersion::query()->create([
                'page_id' => $page->id,
                'version' => $nextVersion,
                'payload' => $payload,
                'checksum' => $checksum,
                'published_by' => $publishedBy,
                'published_at' => now(),
            ]);

            $warmRoutes = $this->syncRoutesInDatabase($page, $version);

            $page->forceFill([
                'published_version_id' => $version->id,
            ])->save();

            return [$version, $page, false, $oldRoutes, $warmRoutes];
        });

        foreach ($oldRoutes as $route) {
            $this->routeCache->forget(
                $route['tenant_key'],
                $route['locale'],
                $route['slug'],
            );
        }

        $pointerKey = $this->cache->key(
            'pointer',
            'page',
            $page->tenant_key,
            $page->page_key,
        );

        $this->cache->forget($pointerKey);

        if ($wentOffline || $version === null) {
            event(new PagePublished(
                tenantKey: $page->tenant_key,
                pageId: $page->id,
                versionId: 0,
                publishedBy: $publishedBy,
            ));

            return null;
        }

        foreach ($warmRoutes as $route) {
            $this->routeCache->put(
                $route['tenant_key'],
                $route['locale'],
                $route['slug'],
                $route['page_id'],
                $route['page_key'],
            );
        }

        $this->cache->put(
            $pointerKey,
            [
                'page_id' => $page->id,
                'version_id' => $version->id,
            ],
            RuntimeCache::TTL_POINTER,
        );

        $this->cache->put(
            $this->cache->key(
                'page-version',
                (string) $page->id,
                (string) $version->id,
            ),
            $version->payload,
            RuntimeCache::TTL_VERSION,
        );

        event(new PagePublished(
            tenantKey: $page->tenant_key,
            pageId: $page->id,
            versionId: $version->id,
            publishedBy: $publishedBy,
        ));

        return $version;
    }

    /**
     * @return list<array{
     *     tenant_key: string,
     *     locale: string,
     *     slug: string,
     *     page_id: int,
     *     page_key: string
     * }>
     */
    private function syncRoutesInDatabase(UiPage $page, UiPageVersion $version): array
    {
        UiPageRoute::query()
            ->where('page_id', $page->id)
            ->delete();

        $warmRoutes = [];

        foreach ($version->payload['page']['translations'] ?? [] as $locale => $translation) {
            $slug = $translation['slug'] ?? null;

            if (! $slug) {
                continue;
            }

            UiPageRoute::query()->create([
                'tenant_key' => $page->tenant_key,
                'page_id' => $page->id,
                'page_version_id' => $version->id,
                'locale' => $locale,
                'slug' => $slug,
            ]);

            $warmRoutes[] = [
                'tenant_key' => $page->tenant_key,
                'locale' => (string) $locale,
                'slug' => (string) $slug,
                'page_id' => $page->id,
                'page_key' => $page->page_key,
            ];
        }

        return $warmRoutes;
    }
}
