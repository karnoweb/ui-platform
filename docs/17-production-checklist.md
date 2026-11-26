# Production Checklist

[← فهرست](README.md)

## زیرساخت

- [ ] Redis: `TENANT_UI_CACHE_STORE=redis`
- [ ] TTL pointer/route ≤ 300s در Production
- [ ] Stampede lock فعال
- [ ] Queue/monitoring برای failed cache (log warnings)

## HTTP / Multi-Tenant

- [ ] `TENANT_UI_RUNTIME_CACHE_VISIBILITY=private` مگر tenant per-domain
- [ ] Runtime API پشت middleware tenant + rate limit
- [ ] `TenantResolver` از context امن

## Data Provider

- [ ] Query budget per page documented در Host App
- [ ] Eager loads reviewed
- [ ] Telescope/Debugbar off in prod

## Best Practices

1. **همیشه** `TenantUi::for($tenant)` — نه tenant پراکنده.
2. Variant را در DB تعریف نکنید.
3. Business logic در Provider/Action — نه Blade.
4. Page Builder را آزاد نکنید — PageType rules sacred.
5. قبل از disable module → `impact()`.
6. Global Publish قبل از اولین Page Publish.
7. Variant lifecycle: deprecate نه delete.

## Anti-Patterns

| ❌ | ✅ |
| --- | --- |
| خواندن `tenant_ui_configs` برای Live site | `runtime()->byPageKey()` |
| Publish page بدون global published | Global publish first |
| `is_active=false` انتظار فوری offline | Publish page |
| `Cache-Control: public` + tenant header | private یا per-host CDN key |
| Catch همه Throwable در host و swallow | map known TenantUi exceptions |
| Direct edit `ui_page_versions` | Draft + publish |

## Performance Model

```text
Warm runtime request ≈ 0 DB (Core)
Cold ≈ O(1) page + O(1) global queries + Redis
Render ≈ Σ Provider queries
```

Load test پیشنهادی قبل از launch:

```text
warm byPageKey / bySlug
cold cache
publish under traffic
100–500 concurrent (k6/ab)
metrics: p95, p99, queries/req, redis ops
```

## محدودیت Atomic Release

سناریو:

```text
Theme A → B
Pages هنوز برای A ساخته شده
```

ممکن است Global Publish block شود یا Page Publish با global قدیمی conflict — تا `tenant_ui_releases` نیامده:

- migrate pages تدریجی
- یا batch republish بعد از global

→ [Overview](01-overview.md)

## Security Runtime API

Published-only — Draft leak ندارد. Auth برای mutate در Dashboard جدا از API read.

→ [Runtime HTTP API](12-runtime-http-api.md)
