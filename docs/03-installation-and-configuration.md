# نصب و تنظیمات

[← فهرست](README.md)

## نصب

```bash
composer require karnoweb/laravel-tenant-ui
```

Service Provider با package discovery ثبت می‌شود:

```text
KarnoWeb\TenantUi\TenantUiServiceProvider
```

Publish config (اختیاری):

```bash
php artisan vendor:publish --tag=tenant-ui-config
```

Migrationها خودکار load می‌شوند:

```bash
php artisan migrate
```

## فایل `config/tenant-ui.php`

### Cache

```php
'cache' => [
    'store' => env('TENANT_UI_CACHE_STORE'), // null = cache.default
    'prefix' => 'tenant-ui',
    'ttl_seconds' => 86400, // fallback profile
    'ttl' => [
        'pointer' => 300,   // mutable: global/page pointer
        'route'   => 300,   // mutable: slug → page
        'version' => 604800, // immutable snapshots
        'runtime' => 86400,  // merged localized payload
    ],
    'stampede' => [
        'enabled' => true,
        'profiles' => ['pointer', 'route', 'runtime'],
        'lock_seconds' => 10,
        'block_seconds' => 5,
    ],
],
```

### Defaults

```php
'defaults' => [
    'theme' => null,
    'components' => [
        'header' => null,
        'footer' => null,
    ],
],
```

### Runtime API

```php
'runtime_api' => [
    'enabled' => false,
    'prefix' => 'api/ui',
    'middleware' => ['api'],
    'cache_max_age' => 300,
    'cache_visibility' => 'private', // public فقط برای tenant per-host
],
```

### Resolvers

```php
'resolvers' => [
    'tenant' => null,  // class implementing TenantResolverContract
    'modules' => null, // ModuleResolverContract — الزامی برای compatibility
    'locale' => null,  // default: DefaultLocaleManager
],
```

## Environment Variables

```env
TENANT_UI_CACHE_STORE=redis

TENANT_UI_CACHE_POINTER_TTL=300
TENANT_UI_CACHE_ROUTE_TTL=300
TENANT_UI_CACHE_VERSION_TTL=604800
TENANT_UI_CACHE_RUNTIME_TTL=86400

TENANT_UI_CACHE_STAMPEDE_LOCK=true
TENANT_UI_CACHE_STAMPEDE_LOCK_SECONDS=10
TENANT_UI_CACHE_STAMPEDE_BLOCK_SECONDS=5

TENANT_UI_RUNTIME_CACHE_MAX_AGE=300
TENANT_UI_RUNTIME_CACHE_VISIBILITY=private
```

Production: **Redis** برای cache store توصیه می‌شود.

## Facade

Alias: `TenantUi` → `KarnoWeb\TenantUi\Facades\TenantUi` (در `composer.json` extra.laravel).

→ [اتصال Host App](04-host-app-integration.md)
