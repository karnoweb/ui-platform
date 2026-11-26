# Cache و Performance

[← فهرست](README.md)

## لایه‌های Cache

| Key pattern | Mutable? | TTL پیش‌فرض | محتوا |
| --- | --- | --- | --- |
| `pointer:global:{tenant}` | بله | 300s | `published_version_id` |
| `pointer:page:{tenant}:{pageKey}` | بله | 300s | page_id + version_id |
| `route:{tenant}:{locale}:{hash}` | بله | 300s | page_id + page_key |
| `global:{tenant}:{versionId}` | خیر | 7d | Global payload JSON |
| `page-version:{pageId}:{versionId}` | خیر | 7d | Page payload JSON |
| `runtime:...` | نسبتاً | 86400s | merged localized payload |

TTL mutable کوتاه: بعد از outage Redis، pointer/route stale حداکثر چند دقیقه.

## کلید Runtime

```text
runtime:{tenant}:{globalVersion}:{pageVersion}:{requestedLocale}:{fallbackLocale}
```

تغییر fallback Tenant بدون bump version → cache entry جدا.

## `RuntimeCache::remember()`

```text
try get → hit? return
miss → loader()  // خارج از catch cache
     → try put
return value
```

- خطای **loader** (DB) یک بار propagate — **دوبار execute نمی‌شود**.
- خطای get/put → log + miss یا skip write؛ loader همچنان جواب می‌دهد.

## Stampede protection

برای profileهای `pointer`, `route`, `runtime`:

```text
miss → distributed lock → double-check get → loader → put
```

Config: `tenant-ui.cache.stampede.*`

Timeout lock → load بدون write (log).

## Publish و Cache

`PagePublisher` / `TenantUiPublisher`:

- داخل **transaction**: فقط DB
- **بعد از commit**: forget route/pointer قدیم، put snapshot/route/runtime pointer

## RenderDataMemoizer

- **Request-scoped** (scoped binding) — بین requestها share نمی‌شود.
- Default key: tenant, locale, pageKey, sectionInstanceKey, scope, type, config hash, extra hash + **provider class**.
- Custom: `MemoizableDataProviderContract::memoKey()` با prefix **`{ProviderClass}:`**

## Performance Model

| بخش | سقف تقریبی (warm) |
| --- | --- |
| Runtime Core | 0 query (cache hit) |
| Cold miss | چند query + Redis |
| Blade render | + N × DataProvider |

**گلوگاه اصلی Production:** Queryهای Host App در `UiDataProviderContract` — نه Compiler Runtime.

→ [Data Provider Performance](11-rendering-and-data-providers.md#قرارداد-performance)

## تست زیرساخت

```bash
TENANT_UI_INTEGRATION=1 vendor/bin/phpunit --group integration
```

Redis واقعی + stampede (اختیاری در CI).

## آینده

`CacheRepair` command (پیشنهادی): rebuild pointer از DB — هنوز در Package نیست.

→ [Production](17-production-checklist.md)
