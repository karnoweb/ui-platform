# Checklist، فازها و اشتباهات

[← فهرست Blade](README.md) · [Production](../17-production-checklist.md)

## Checklist اولین Theme Core

- [ ] `core` ThemeDefinition + master + Vite entry + CSS
- [ ] `core-header-01`, `core-footer-01`, `core-hero-01`
- [ ] Preset `core-default`, PageTypes home/about/contact/landing
- [ ] ModuleSeeder + ModuleResolver + LocaleManager
- [ ] TenantUiBootstrapSeeder
- [ ] `php artisan tenant-ui:doctor`
- [ ] Global Publish + Home Publish + Blade smoke

## Checklist هر Section جدید

```text
type · variant key · module · page types
config schema · translation schema
data contract · Query · Provider · Blade
optional assets · cache strategy
```

مثال `shop-categories-grid-01`: type=`categories`, module=`shop`, data=`categories[]`.

## Checklist API Headless

- [ ] Runtime API + secure TenantResolver
- [ ] ETag + private/public decision
- [ ] Frontend variant registry (map key → component)
- [ ] Business API per module
- [ ] Shared Domain Query (Blade + API)
- [ ] Runtime config ≠ business data در مستند تیم

## ترتیب توسعه پروژه (۱۲ فاز)

```text
1  Module System
2  Core Theme + Vite + Header/Footer
3  TenantUiDefinitionServiceProvider
4  Module UI Variants (shop/stories/blog)
5  Queries + DataProviders
6  TenantUiBootstrapSeeder
7  Public Blade (Home + dynamic slug)
8  Dashboard Appearance
9  Dashboard Page Builder
10 Preview + Publish flows
11 Production cache review
12 Optional Runtime API / Next
```

## اشتباهات رایج

| # | اشتباه | درست |
| --- | --- | --- |
| 1 | Theme در DB seed | ThemeDefinition در Provider |
| 2 | Provider per variant | Provider per **type** |
| 3 | CSS path در `tenant_ui_configs` | Vite + master layout |
| 4 | Cache products در Runtime payload | Business cache جدا |
| 5 | `Product::query()` در Blade | DataProvider / Query |
| 6 | Disable module بدون impact | `assertCanDisableModule` |
| 7 | Deploy frontend per tenant | یک codebase + Published UI |

## نتیجه — چهار لایه ذهنی

```text
Registry     → چه UI در code هست
Seeder/Dashboard → Tenant چه انتخاب کرد
Runtime      → Published version چیست
BladeRenderer + Provider + Vite → چگونه render + asset
ModuleResolver → feature فعال است؟
Cache        → Config=Package · Data=Host
```

با این مرزها Theme/Module مستقل، Blade نازک، Runtime سریع، و Headless بدون بازنویسی Core اضافه می‌شود.

---

[← شروع: هدف و مرز](01-goals-and-boundaries.md)
