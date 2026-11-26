# اتصال Host App

[← فهرست](README.md)

Package عمداً Tenant Model، Module System و Locale System پروژه را نمی‌شناسد. اتصال فقط از **Contract** و **Definition Registry** است.

## TenantResolverContract

برای **Runtime HTTP API** (تشخیص tenant از request):

```php
use KarnoWeb\TenantUi\Contracts\TenantResolverContract;

final class TenantResolver implements TenantResolverContract
{
    public function currentKey(): string
    {
        return (string) app('currentTenant')->getKey();
    }
}
```

در Dashboard/Job معمولاً مستقیماً `TenantUi::for($tenantKey)` صدا می‌زنید؛ Resolver برای routeهای API لازم است.

## ModuleResolverContract

**الزامی** برای هر Variant با `requiresModules` (و برای Impact/Disable):

```php
use KarnoWeb\TenantUi\Contracts\ModuleResolverContract;

final class ModuleResolver implements ModuleResolverContract
{
    public function enabled(string $tenantKey, string $moduleKey): bool
    {
        return app(ModuleManager::class)
            ->forTenant($tenantKey)
            ->enabled($moduleKey);
    }
}
```

اگر bind نشود: **fail-closed** → `ConfigurationException` در compatibility.

## LocaleManagerContract

```php
use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;

final class TenantLocaleManager implements LocaleManagerContract
{
    public function current(): string { return app()->getLocale(); }

    public function fallback(string $tenantKey): string { return 'fa'; }

    /** @return list<string> */
    public function supported(string $tenantKey): array
    {
        return ['fa', 'en', 'ar'];
    }
}
```

**مهم برای Runtime Cache:** `fallback()` در **cache key** payload ادغام‌شده نقش دارد. تغییر fallback Tenant باید در Host App invalidate یا TTL کوتاه mutable keys را در نظر بگیرد.

→ [Cache](10-cache-and-performance.md#کلید-runtime)

## ثبت در config

```php
// config/tenant-ui.php
'resolvers' => [
    'tenant' => \App\TenantUi\TenantResolver::class,
    'modules' => \App\TenantUi\ModuleResolver::class,
    'locale' => \App\TenantUi\TenantLocaleManager::class,
],
```

## Service Provider پیشنهادی Host App

```text
App\Providers\TenantUiDefinitionServiceProvider
```

در `boot()`:

- Theme / Variant / Preset / PageType / DataProvider را register کنید.
- Resolverها را ترجیحاً در `AppServiceProvider` یا config bind کنید.

→ [ثبت Definitionها](05-registering-definitions.md)

## Checklist اتصال

- [ ] Migration اجرا شده
- [ ] `ModuleResolverContract` bind شده
- [ ] `LocaleManagerContract` برای multi-locale
- [ ] Definition Service Provider در `bootstrap/providers.php`
- [ ] Redis + `TENANT_UI_CACHE_STORE` در Production
- [ ] Runtime API: `TenantResolver` + middleware tenant (در صورت فعال‌سازی)

→ [Production Checklist](17-production-checklist.md)
