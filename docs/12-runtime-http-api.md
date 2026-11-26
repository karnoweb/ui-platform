# Runtime HTTP API

[← فهرست](README.md)

## فعال‌سازی

```php
// config/tenant-ui.php
'runtime_api' => [
    'enabled' => true,
    'prefix' => 'api/ui',
    'middleware' => ['api'], // tenant middleware خودتان را اضافه کنید
],
```

Routeها از `routes/api.php` package load می‌شوند.

## Endpoints

| Method | Path | Controller |
| --- | --- | --- |
| GET | `{prefix}/pages/{pageKey}` | `RuntimePageController` |
| GET | `{prefix}/resolve?slug=&locale=` | `RuntimeResolveController` |

Query: `locale` (optional — default از `LocaleManagerContract`).

Tenant از `TenantResolverContract::currentKey()`.

## ETag و 304

Response شامل `ETag` مطابق `RuntimePayloadService::etag()`.

Client:

```http
If-None-Match: "global-4-page-18-fa-fa"
```

→ `304 Not Modified` بدون body.

## Cache-Control

Helper: `RuntimeApiCacheHeaders::cacheControl()`

| Config | Header |
| --- | --- |
| `cache_visibility=private` (default) | `private, max-age=300` |
| `cache_visibility=public` | `public, max-age=300` |

**`public` فقط** وقتی tenant از **host/path** جداست (مثلاً `shop-a.com`).  
اگر tenant از header/session است → **`private`** وگرنه CDN پاسخ Tenant A را به B می‌دهد.

## Next / Nuxt

```text
GET /api/ui/pages/home?locale=fa
→ payload JSON
→ map sections → Vue components by variant key
```

- Global components از `payload.components` + theme metadata Host App.
- Revalidate با ETag / `global_version` + `page_version`.

→ [Preview، Publish، Runtime](09-preview-publish-runtime.md)

## Security

- Runtime API فقط **Published** — Draft در API نیست.
- Rate limit و auth در middleware Host App.
- Resolver tenant را از context امن استخراج کند (نه query param خام).

→ [Production Checklist](17-production-checklist.md)
