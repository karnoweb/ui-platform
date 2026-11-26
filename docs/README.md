# مستندات `karnoweb/laravel-tenant-ui`

> **نسخه API:** `1.1.0` · **PHP:** `8.2+` · **Laravel:** `11 / 12 / 13`

پلتفرم UI چندمستاجری (Multi-Tenant) با **Draft / Publish**، **Versioning**، **Runtime Payload** و **Page Builder** کنترل‌شده.

---

## از کجا شروع کنم؟

| نقش | مسیر پیشنهادی |
| --- | --- |
| توسعه‌دهنده تازه‌وارد | [Quick Start](18-quick-start-walkthrough.md) → [نصب](03-installation-and-configuration.md) → [Host App](04-host-app-integration.md) |
| ساخت Dashboard | [API با Scope](06-scoped-api.md) → [Page Builder](08-page-builder.md) → [Dashboard](16-dashboard-and-operations.md) |
| Frontend (Blade) | [Runtime](09-preview-publish-runtime.md) → [Blade & Provider](11-rendering-and-data-providers.md) |
| Frontend (Next/Nuxt) | [Runtime HTTP API](12-runtime-http-api.md) |
| DevOps / Production | [Cache & Performance](10-cache-and-performance.md) → [Production](17-production-checklist.md) |

---

## فهرست مستندات

### مقدمه و مفاهیم

| # | سند | محتوا |
| --- | --- | --- |
| 1 | [نمای کلی](01-overview.md) | مسئله، اهداف، مدل ذهنی |
| 2 | [مفاهیم هسته](02-core-concepts.md) | Theme، Variant، Preset، Page Type، Draft/Published |

### راه‌اندازی

| # | سند | محتوا |
| --- | --- | --- |
| 3 | [نصب و تنظیمات](03-installation-and-configuration.md) | Composer، config، env |
| 4 | [اتصال Host App](04-host-app-integration.md) | Resolverها، Service Provider |
| 5 | [ثبت Definitionها](05-registering-definitions.md) | Theme، Variant، Preset، Page Type، Provider |

### API و جریان کار

| # | سند | محتوا |
| --- | --- | --- |
| 6 | [API با Tenant Scope](06-scoped-api.md) | `TenantUi::for()`، Registryها |
| 7 | [Global UI](07-global-ui.md) | Draft، Publish، Impact |
| 8 | [Page Builder](08-page-builder.md) | Section، Translation، Revision |
| 9 | [Preview، Publish، Runtime](09-preview-publish-runtime.md) | Payload، `is_active`، قوانین سازگاری |

### زیرساخت و رندر

| # | سند | محتوا |
| --- | --- | --- |
| 10 | [Cache و Performance](10-cache-and-performance.md) | Pointer، Stampede، TTL، Memoizer |
| 11 | [Blade و Data Provider](11-rendering-and-data-providers.md) | `BladeRenderer`، قرارداد Query |
| 12 | [Runtime HTTP API](12-runtime-http-api.md) | ETag، 304، Cache-Control |

### مرجع

| # | سند | محتوا |
| --- | --- | --- |
| 13 | [Module Impact](13-module-impact-and-compatibility.md) | Global + Pages، Disable Module |
| 14 | [Database](14-database.md) | جداول، روابط، چرا Definition در DB نیست |
| 15 | [Exception و Event](15-exceptions-events.md) | خطاها، رویدادها |
| 16 | [Dashboard و عملیات](16-dashboard-and-operations.md) | ساختار UI، Doctor، تست |
| 17 | [Production Checklist](17-production-checklist.md) | Redis، امنیت، Anti-Pattern |
| 18 | [Quick Start](18-quick-start-walkthrough.md) | راه‌اندازی end-to-end یک Tenant |
| — | [واژه‌نامه](glossary.md) | اصطلاحات |

---

## راهنمای عملی Blade (Host App)

مسیر جدا برای پیاده‌سازی **Laravel + Blade + Vite + Dashboard** در پروژه Multi-Tenant:

→ **[docs/blade/README.md](blade/README.md)**

موضوعات: Theme Core، Seeder/Module، Vite، DataProvider/Query، Cache سه‌لایه، Controller، API vs Business Data، Next/Nuxt، Checklist.

---

## محدودیت‌های نسخه فعلی

- **`tenant_ui_releases` وجود ندارد** — Publish اتمیک «کل سایت یکجا» پیاده‌سازی نشده؛ Global و Page مستقل Publish می‌شوند.
- **Performance واقعی صفحه** به **DataProvider**های Host App وابسته است؛ Core فقط Runtime و Memoizer request-scoped را تضمین می‌کند.

---

## مستند قدیمی

فایل تک‌پارچه [`../laravel-tenant-ui-documentation-fa.md`](../laravel-tenant-ui-documentation-fa.md) نگه‌داری می‌شود؛ **منبع به‌روز: همین پوشه `docs/`.**
