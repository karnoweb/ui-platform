<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Closure;
use KarnoWeb\TenantUi\Models\UiPageRoute;

final readonly class RouteCacheService
{
    public function __construct(
        private RuntimeCache $cache,
    ) {
    }

    public function key(string $tenantKey, string $locale, string $slug): string
    {
        return $this->cache->key(
            'route',
            $tenantKey,
            $locale,
            sha1($slug),
        );
    }

    /**
     * @return array{page_id: int, page_key: string}
     */
    public function remember(
        string $tenantKey,
        string $locale,
        string $slug,
        Closure $loader,
    ): array {
        return $this->cache->remember(
            $this->key($tenantKey, $locale, $slug),
            $loader,
            RuntimeCache::TTL_ROUTE,
        );
    }

    public function put(
        string $tenantKey,
        string $locale,
        string $slug,
        int $pageId,
        string $pageKey,
    ): void {
        $this->cache->put(
            $this->key($tenantKey, $locale, $slug),
            [
                'page_id' => $pageId,
                'page_key' => $pageKey,
            ],
            RuntimeCache::TTL_ROUTE,
        );
    }

    public function forget(string $tenantKey, string $locale, string $slug): void
    {
        $this->cache->forget($this->key($tenantKey, $locale, $slug));
    }

    public function forgetForPage(int $pageId): void
    {
        UiPageRoute::query()
            ->where('page_id', $pageId)
            ->get(['tenant_key', 'locale', 'slug'])
            ->each(function ($route): void {
                $this->forget(
                    (string) $route->tenant_key,
                    (string) $route->locale,
                    (string) $route->slug,
                );
            });
    }
}
