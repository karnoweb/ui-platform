# API، Headless و Next/Nuxt

[← فهرست Blade](README.md) · [Runtime HTTP API](../12-runtime-http-api.md)

## Runtime API — چه می‌دهد؟

با `runtime_api.enabled`:

```http
GET /api/ui/pages/home?locale=fa
GET /api/ui/resolve?slug=about-us&locale=fa
```

JSON شامل:

```text
theme · components · page meta · sections[]
  → type, variant, config, content (translation)
```

## نکته حیاتی: UI Config ≠ Business Data

Runtime API **DataProvider اجرا نمی‌کند**.

```text
products / stories / posts
```

خودکار در payload **نیست** — عمدی است.

| API | محتوا |
| --- | --- |
| Package Runtime API | ساختار UI + متن ترجمه‌شده section |
| Host Business API | Product، Story، Post، Cart، … |

## Next/Nuxt — روش پیشنهادی

```text
GET /api/ui/pages/home     → layout + section configs
GET /api/catalog/products?source=best_sellers&limit=12  → data
```

Component map: `variant key` → Vue component؛ data از **module API** با همان `config`.

## Aggregator یک‌جا (اختیاری)

```text
GET /api/site/home  → config + hydrated data
```

Host App: `PageApiAssembler`

```text
runtime payload
→ foreach section type → shared Query (همان ProductSectionQuery)
→ attach data
```

Blade Provider و Assembler **یک Query** — duplicate logic ممنوع.

## چرا Provider را مستقیم در API صدا نزنیم؟

Provider adapter رندر Blade است؛ Domain Query مستقل برای Blade / API / Job / CLI بهتر است.

## Tenant Resolution API

✅ `shop-a.com` → Tenant A  
✅ `/api/{tenant}/...` با authorization  
❌ `?tenant_id=` بدون validation

## HTTP Cache

Package: ETag، `If-None-Match` → 304، `Cache-Control`.

- Tenant per domain → `TENANT_UI_RUNTIME_CACHE_VISIBILITY=public` (با احتیاط)
- Tenant از session/header → **`private`**

## معماری API (خلاصه)

```text
Next → Runtime UI API → variant/config/content
     → Module APIs → shared Query/Cache
```

یا:

```text
Next → Host PageAssembler → hydrated JSON
```

→ [Checklist Headless](14-checklists-workflow.md#checklist-api-headless)
