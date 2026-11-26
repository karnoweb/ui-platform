# Seeder، Module و Bootstrap UI

[← فهرست Blade](README.md)

## Theme را Seeder نکنید

Theme **Definition در کد** است (`ThemeRegistry`) — نه جدول `themes`.

```text
Theme Definition     → TenantUiDefinitionServiceProvider
Tenant uses core     → tenant_ui_configs (Draft) + Publish
```

Seeder فقط می‌تواند بگوید: «Tenant A → theme `core` + preset → Publish اولیه».

## Moduleها — Host Seeder

جدول/مدل Module در Host App:

```php
// ModuleSeeder — نمونه
foreach (['shop' => 'فروشگاه', 'stories' => 'استوری', 'blog' => 'وبلاگ', 'search' => 'جستجو'] as $key => $name) {
    Module::query()->updateOrCreate(['key' => $key], ['name' => $name]);
}
```

Package فقط **key** (`shop`, `stories`, …) را در `requiresModules` مصرف می‌کند.

## فعال‌سازی Module برای Tenant

```php
$moduleManager->forTenant($tenantKey)->enable('shop');
```

یا Pivot در `TenantModuleSeeder`.  
`ModuleResolverContract::enabled($tenantKey, $moduleKey)` باید همان منطق را reflect کند.

## ترتیب Seeder در `DatabaseSeeder`

```php
$this->call([
    ModuleSeeder::class,
    TenantModuleSeeder::class,      // enable modules per tenant
    TenantUiBootstrapSeeder::class, // global + home publish
]);
```

**چرا:** Publish و Add Section → `requiresModules` — Module باید **قبل از UI Publish** فعال باشد.

## TenantUiBootstrapSeeder (نمونه منطق)

```text
foreach tenants:
  global()->draft()->save(core, core-default, …)
  global()->publish()
  create home if missing + fa translation + optional hero section
  pages()->publish(home)
```

Theme را **ایجاد نمی‌کند** — فقط انتخاب Tenant را در Package می‌نویسد.

## چه چیز Seeder است / نیست

| مورد | Seeder؟ | محل |
| --- | ---: | --- |
| Theme / Variant / Preset / PageType Definition | ❌ | Service Provider |
| Module catalog (DB) | ✅ | ModuleSeeder |
| Tenant ↔ Module | ✅ | TenantModuleSeeder / Admin |
| Initial theme + home publish | ✅ اختیاری | TenantUiBootstrapSeeder |
| Page Builder edits | ❌ | Dashboard |

## MVP Core Bootstrap (حداقل Definition)

```text
Theme: core · Preset: core-default
Global: core-header-01, core-footer-01
Sections: core-hero-01, core-banner-01
Modules (Host): shop, stories, blog
Shop UI: shop-products-grid-01
Stories: stories-strip-01 · Blog: blog-latest-01
PageTypes: home, about, contact, landing
```

→ [Theme Core](05-theme-vite-assets.md)
