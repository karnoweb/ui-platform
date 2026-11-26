# Resolverها و Config

[← فهرست Blade](README.md) · [Host App (Core)](../04-host-app-integration.md)

## TenantResolver

```php
final class TenantResolver implements TenantResolverContract
{
    public function currentKey(): string
    {
        return (string) app('currentTenant')->getKey();
    }
}
```

برای **Runtime HTTP API** ضروری؛ در Dashboard معمولاً `TenantUi::for($tenantKey)`.

## ModuleResolver

```php
public function enabled(string $tenantKey, string $moduleKey): bool
{
    return $this->modules->forTenant($tenantKey)->enabled($moduleKey);
}
```

Variant با `requiresModules` + Resolver bind نشده → **ConfigurationException** (fail-closed).

## LocaleManager

```php
public function current(): string;
public function fallback(string $tenantKey): string;
public function supported(string $tenantKey): array;
```

Fallback در **Runtime cache key** Package نقش دارد — تغییر fallback Tenant را در Host track کنید.

## config/tenant-ui.php

```php
'resolvers' => [
    'tenant' => \App\TenantUi\Resolvers\TenantResolver::class,
    'modules' => \App\TenantUi\Resolvers\ModuleResolver::class,
    'locale' => \App\TenantUi\Resolvers\LocaleManager::class,
],
```

Production env (خلاصه):

```env
TENANT_UI_CACHE_STORE=redis
TENANT_UI_CACHE_POINTER_TTL=300
TENANT_UI_CACHE_ROUTE_TTL=300
TENANT_UI_CACHE_STAMPEDE_LOCK=true
TENANT_UI_RUNTIME_CACHE_VISIBILITY=private
```

→ [Cache سه‌لایه](10-caching-three-layers.md)
