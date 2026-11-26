# Cache سه‌لایه

[← فهرست Blade](README.md) · [Runtime Cache Package](../10-cache-and-performance.md)

## لایه ۱ — Runtime Configuration (Package)

```text
Theme · Preset · Sections · Translations · Published versions
pointer · route · runtime merged payload
```

Host کاری نمی‌کند — Redis + TTL/stampede در config.

**Runtime payload = UI configuration** — نه لیست Product.

## لایه ۲ — Request Memoizer (Package)

`RenderDataMemoizer` — scoped per HTTP request.  
Provider duplicate در یک request → یک بار `provide()`.

## لایه ۳ — Business Data (Host App)

Products، Categories، Menu، Stories، Posts → در **Query Service** cache کنید.

### Versioned catalog cache

```text
tenant:{id}:catalog-version = 41

ui:data:products:{tenant}:{locale}:41:{configHash}
```

روی `ProductSaved` / delete:

```php
Cache::increment("tenant:{$tenantKey}:catalog-version");
```

بدون `Cache::flush()` سنگین.

### Cache نکنید (shared)

Cart، user price، notifications، personalization — از `RenderContext::$extra` + auth.

## داده مشترک به Renderer (`extra`)

Menu/settings یک بار:

```php
$renderExtra = [
    'shared' => app(SiteSharedDataService::class)->getCached($tenantKey, $locale),
];
TenantUi::render()->renderGlobal($payload, 'header', $renderExtra);
TenantUi::render()->renderSections($payload, $renderExtra);
```

Provider:

```php
$shared = $context->extra['shared'] ?? [];
```

## Responsibility Map (Production)

| لایه | مالک |
| --- | --- |
| Runtime cache | Package |
| Memoizer | Package |
| Products/Stories cache | Host App |
| CDN | Infra |
| Browser | ETag / Cache-Control |

→ [API — چرا Product در Runtime JSON نیست](12-api-headless-next.md)
