# karnoweb/laravel-tenant-ui

**مستندات فارسی (ساختاریافته):** [`docs/README.md`](docs/README.md)  
**راهنمای عملی Blade / Host App:** [`docs/blade/README.md`](docs/blade/README.md)

## API scoped by tenant

```php
use KarnoWeb\TenantUi\Facades\TenantUi;

$ui = TenantUi::for('tenant-key');

$ui->global()->draft()->save(
    themeKey: 'modern',
    presetKey: 'modern-shop',
    overrides: ['header' => 'header-5'],
    expectedRevision: 1,
);

$ui->global()->publish();

$page = $ui->pages()->createPage('home', 'home');
$ui->pages()->publish($page->id);

$payload = $ui->runtime()->byPageKey('home', 'fa');

$ui->impact()->assertCanDisableModule('stories');
```

After `for($tenantKey)`, tenant is not passed again to runtime/impact/global APIs.

## Publish rules

- **Runtime** uses `published_version_id` only (draft `is_active` does not affect live site until publish).
- **Page publish** validates sections against the **published global theme**, not draft global theme.
- **Global publish** re-validates all published pages against the new theme.
- **Module impact** scans published global components and published page sections.

## Module resolver

Bind `ModuleResolverContract` in config; otherwise compatibility checks throw `ConfigurationException`.

## Runtime cache (production)

- `RuntimeCache::remember()` loads data **outside** cache error handling (loaders are not retried on DB failures).
- **Mutable** keys (`pointer`, `route`) default to **300s** TTL; **immutable** version/runtime snapshots use longer TTL.
- Stampede protection uses a short lock on `pointer`, `route`, and `runtime` profiles (`tenant-ui.cache.stampede`).
- Runtime payload cache key includes **requested locale + fallback locale**.
- Runtime API HTTP cache defaults to `Cache-Control: private`; set `TENANT_UI_RUNTIME_CACHE_VISIBILITY=public` only for host/path-isolated tenants.

Infrastructure tests (Redis, optional): `TENANT_UI_INTEGRATION=1 vendor/bin/phpunit --group integration`.

## Tests

```bash
composer test
```
