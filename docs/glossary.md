# واژه‌نامه

[← فهرست](README.md)

| اصطلاح | معنی |
| --- | --- |
| **Tenant** | مستاجر SaaS؛ `tenant_key` string در تمام جداول UI |
| **Theme** | زبان طراحی: layout، capabilities، Blade master layout |
| **Variant** | نسخه قابل انتخاب یک component (global یا section) |
| **Preset** | bundle پیش‌فرض variantهای global برای یک سبک فروشگاه |
| **Page Type** | schema مجاز sectionها (min/max per type) |
| **page_key** | شناسه ثابت صفحه (مستقل از locale) |
| **Draft** | وضعیت editable در DB قبل از Publish |
| **Published Version** | snapshot immutable در `*_versions` |
| **Runtime** | payload merge شده Global+Page برای locale درخواستی |
| **Pointer** | cache entry که به `published_version_id` اشاره می‌کند |
| **Module** | feature flag منطقی Host App (`stories`, `search`, …) |
| **Compatibility** | validate variant/theme/module/page type |
| **Impact** | وابستگی module در published global/pages |
| **ETag** | fingerprint نسخه برای conditional HTTP |
| **Stampede** | thundering herd روی cache miss — با lock mitigated |
| **Data Provider** | کلاس Host App برای query داده section/global |
| **Memoizer** | dedupe provider در یک HTTP request |
| **Atomic Release** | publish یکجای global+pages — **فعلاً نیست** |
