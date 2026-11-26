<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Contracts\TenantResolverContract;
use KarnoWeb\TenantUi\Services\RuntimePayloadService;
use KarnoWeb\TenantUi\Support\RuntimeApiCacheHeaders;

final readonly class RuntimeResolveController
{
    public function __construct(
        private RuntimePayloadService $runtime,
        private TenantResolverContract $tenants,
        private LocaleManagerContract $locales,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tenantKey = $this->tenants->currentKey();

        $data = $request->validate([
            'slug' => ['required', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:20'],
        ]);

        $locale = $data['locale'] ?? $this->locales->current();

        abort_unless(
            in_array($locale, $this->locales->supported($tenantKey), true),
            422,
            'Unsupported locale.'
        );

        $payload = $this->runtime->bySlug(
            tenantKey: $tenantKey,
            slug: $data['slug'],
            locale: $locale,
        );

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
