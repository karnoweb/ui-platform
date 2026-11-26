# هدف و مرز مسئولیت

[← فهرست Blade](README.md) · [فهرست کل](../README.md)

## در پایان چه داریم؟

سه Tenant روی یک Backend:

```text
Shop A — core — header-01 — Home: Hero, Story, Products, Banner
Shop B — core — header-02 — Home: Hero, Products, Story (ترتیب متفاوت)
Shop C — core — header-01 — Home: Hero, Products (بدون stories)
```

تفاوت‌ها از **Draft/Publish در Package** + **Module enable در Host** می‌آید — نه deploy جدا per shop.

## Package مدیریت می‌کند

```text
Theme / Variant / Preset / PageType Registry
Tenant UI Config، Pages، Sections، Translations
Draft · Preview · Publish
Runtime Payload · Versioning · Runtime Cache
Compatibility · Module Impact
BladeRenderer · Runtime JSON API (اختیاری)
```

## Host App مدیریت می‌کند

```text
Tenant Model · Module System · Auth · Permissions
Products · Stories · Blog · Cart · Media · Menus
Vite · CSS/JS compile · Admin Dashboard
Business Query · Business Cache
```

Package **Product/Story را نمی‌شناسد** — فقط می‌داند:

```text
section type = products
variant      = shop-products-grid-01
requires     = shop
```

داده واقعی → **DataProvider + Query Service** در Host App.

## ذهنیت چهار سؤال

| سؤال | پاسخ |
| --- | --- |
| چه UIهایی وجود دارند؟ | **Registry (کد)** |
| Tenant کدام را انتخاب کرده؟ | **DB + Dashboard/Seeder** |
| Live site چه نسخه‌ای است؟ | **Runtime (Published)** |
| View چه data می‌گیرد؟ | **DataProvider → Query** |

→ [ساختار پوشه‌ها](02-project-structure.md)
