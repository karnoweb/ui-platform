<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Contracts\TenantResolverContract;
use KarnoWeb\TenantUi\Services\RuntimePayloadService;
use KarnoWeb\TenantUi\Support\RuntimeApiCacheHeaders;

final readonly class RuntimePageController
{
    public function __construct(
        private RuntimePayloadService $runtime,
        private TenantResolverContract $tenants,
        private LocaleManagerContract $locales,
    ) {
    }

    public function __invoke(
        Request $request,
        string $pageKey,
    ): JsonResponse {
        $tenantKey = $this->tenants->currentKey();
        $locale = (string) $request->query(
            'locale',
            $this->locales->current(),
        );

        abort_unless(
            in_array($locale, $this->locales->supported($tenantKey), true),
            422,
            'Unsupported locale.'
        );

        $payload = $this->runtime->byPageKey(
            tenantKey: $tenantKey,
            pageKey: $pageKey,
            locale: $locale,
        );

        return $this->jsonWithCache($request, $payload);
    }

    private function jsonWithCache(Request $request, array $payload): JsonResponse
    {
        $etag = $this->runtime->etag($payload);
        $cacheControl = RuntimeApiCacheHeaders::cacheControl();

        if ($request->headers->get('If-None-Match') === '"'.$etag.'"') {
            return response()
                ->noContent(304)
                ->setEtag($etag)
                ->header('Cache-Control', $cacheControl);
        }

        return response()
            ->json($payload)
            ->setEtag($etag)
            ->header('Cache-Control', $cacheControl);
    }
}
