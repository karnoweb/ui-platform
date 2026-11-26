# API با Tenant Scope

[← فهرست](README.md)

## نقطه ورود

```php
use KarnoWeb\TenantUi\Facades\TenantUi;

$ui = TenantUi::for('tenant-key');
```

بعد از `for()` **tenantKey** را دوباره به runtime/impact/pages/global ندهید.

## سطح Tenant (`TenantUiTenantScope`)

| متد | بازگشت | کاربرد |
| --- | --- | --- |
| `key()` | `string` | کلید tenant |
| `global()` | `TenantGlobalOperations` | Draft + Publish Global |
| `pages()` | `TenantPageBuilder` | CRUD Draft Page |
| `runtime()` | `TenantRuntimeOperations` | Payload منتشرشده |
| `impact()` | `TenantImpactOperations` | Module usage |
| `preview($pageId, $locale?)` | `array` | Payload Draft |
| `catalog()` | `DefinitionCatalogService` | Dashboard metadata |

## Global

```php
$ui->global()->draft()->save(
    themeKey: 'modern',
    presetKey: 'modern-shop',
    overrides: ['header' => 'header-5'],
    expectedRevision: null, // یا revision فعلی
);

$version = $ui->global()->publish(publishedBy: (string) auth()->id());
```

→ [Global UI](07-global-ui.md)

## Pages

```php
$pages = $ui->pages();
$page = $pages->createPage('home', 'home');
$pages->publish($pageId);
```

→ [Page Builder](08-page-builder.md)

## Runtime

```php
$payload = $ui->runtime()->byPageKey('home', 'fa');
$payload = $ui->runtime()->bySlug('about-us', 'en');
```

→ [Preview، Publish، Runtime](09-preview-publish-runtime.md)

## Impact

```php
$ui->impact()->assertCanDisableModule('stories');
$usages = $ui->impact()->moduleUsages('search');
```

→ [Module Impact](13-module-impact-and-compatibility.md)

## Registryهای Global (بدون tenant)

```php
TenantUi::themes();
TenantUi::presets();
TenantUi::variants();
TenantUi::pageTypes();
TenantUi::dataProviders();
TenantUi::render(); // BladeRenderer
TenantUi::version(); // 1.1.0
```

`TenantUiManager` سرویس‌ها را **lazy** از container resolve می‌کند.

## Anti-Pattern

```php
// ❌ tenant تکراری و خطر cross-tenant
$ui->runtime()->byPageKey('other-tenant', 'home', 'fa');

// ✅
TenantUi::for('other-tenant')->runtime()->byPageKey('home', 'fa');
```

→ [Production Checklist → Anti-Patterns](17-production-checklist.md#anti-patterns)
