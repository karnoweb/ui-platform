# راهنمای عملی Blade و Host App

> **نوع سند:** اجرایی — برای توسعه‌دهنده Laravel + Blade + Vite + Multi-Tenant  
> **Package:** `karnoweb/laravel-tenant-ui`  
> **مرجع API:** [`../README.md`](../README.md)

این مسیر مرحله‌به‌مرحله پیاده‌سازی واقعی در Host App را پوشش می‌دهد: Theme Core، Seeder، Vite، Variant، DataProvider، Cache، Controller، Dashboard و Headless.

---

## برای چه کسی است؟

- Backend/Full-stack که **Blade storefront** می‌سازد
- تیمی که **Dashboard Page Builder** را به Package وصل می‌کند
- کسی که می‌خواهد **Next/Nuxt** را بدون قاطی کردن UI Config و Business Data وصل کند

---

## فهرست

| # | سند | موضوع |
| --- | --- | --- |
| 1 | [هدف و مرز مسئولیت](01-goals-and-boundaries.md) | خروجی نهایی، Package vs Host |
| 2 | [ساختار پوشه‌ها](02-project-structure.md) | Convention پیشنهادی `app/` و `resources/` |
| 3 | [Seeder، Module، Bootstrap](03-seeding-bootstrap-modules.md) | چه چیز Seed شود / نشود، ترتیب Seeder |
| 4 | [Resolver و Config](04-resolvers-and-config.md) | Tenant، Module، Locale، env |
| 5 | [Theme Core و Vite](05-theme-vite-assets.md) | تعریف Theme، master layout، asset |
| 6 | [Variant و اتصال Module](06-variants-and-modules.md) | Global/Section، naming، shop/story |
| 7 | [Registration و PageType](07-registration-page-types.md) | Preset، Provider، PageTypeها |
| 8 | [Blade، Controller، Route](08-blade-rendering-controllers.md) | متغیرهای View، Home، dynamic slug |
| 9 | [DataProvider و Query](09-data-providers-and-queries.md) | تفکیک لایه، Data Contract |
| 10 | [Cache سه‌لایه](10-caching-three-layers.md) | Runtime، Memoizer، Business + `extra` |
| 11 | [Dashboard و Publish](11-dashboard-preview-publish.md) | Appearance، Catalog، Preview |
| 12 | [API و Next/Nuxt](12-api-headless-next.md) | UI Config vs Business API، Assembler |
| 13 | [Theme/Module جدید](13-extending-theme-module.md) | Checklist گسترش |
| 14 | [Checklist و ترتیب توسعه](14-checklists-workflow.md) | MVP Core، اشتباهات رایج، فازها |

---

## سناریوی مرجع

```text
shop-a.com / shop-b.com / shop-c.com
→ یک codebase
→ Theme core، Header/ Home متفاوت per tenant
→ Shop C بدون Module stories
```

---

## لینک سریع به Core Docs

- [Scoped API](../06-scoped-api.md)
- [Page Builder](../08-page-builder.md)
- [Cache Package](../10-cache-and-performance.md)
- [Runtime HTTP API](../12-runtime-http-api.md)

---

## مستند تک‌فایل قدیمی

[`../../laravel-tenant-ui-practical-blade-guide-fa.md`](../../laravel-tenant-ui-practical-blade-guide-fa.md) → redirect؛ **منبع به‌روز: این پوشه.**
