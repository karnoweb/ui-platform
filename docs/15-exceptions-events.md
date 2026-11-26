# Exception و Event

[← فهرست](README.md)

## Exceptionها

### `DraftConflictException`

Revision `expectedRevision` با DB یکی نیست — Dashboard باید reload کند.

### `CompatibilityException`

Variant/PageType/Module/Theme/schema نقض rule — در Publish یا Draft mutation.

### `ConfigurationException`

مثلاً ModuleResolver bind نشده — fail-closed.

### `PublishedVersionNotFoundException`

Tenant/page/route منتشرشده نیست — Runtime 404.

### `GlobalPublishImpactException`

Global Publish با pageهای published ناسازگار — `$exception->pages`.

### `ModuleDisableImpactException`

Module هنوز در Global/Page published استفاده می‌شود — ساختار usage در exception.

### `TenantUiException`

Base / Blade view missing / provider invalid.

## Eventها

### `TenantUiPublished`

```php
tenantKey, versionId, publishedBy
```

### `PagePublished`

```php
tenantKey, pageId, versionId, publishedBy
```

(`versionId` ممکن است 0 وقتی page offline publish شود.)

## Handling پیشنهادی Dashboard

| Exception | UX |
| --- | --- |
| DraftConflict | «Someone else edited» + reload |
| Compatibility | نمایش field/variant خطا |
| GlobalPublishImpact | لیست page_keyهای blocker |
| ModuleDisableImpact | لیست global + pages usage |
