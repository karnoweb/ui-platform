# گسترش Theme و Module UI

[← فهرست Blade](README.md)

## Theme جدید (`minimal`)

```text
1. resources/views/website/themes/minimal/master.blade.php
2. resources/js/themes/minimal/app.js (+ css)
3. vite.config input
4. ThemeDefinition::register
5. Variantهای compatible یا reuse global keys
6. Preset جدید
7. tenant-ui:doctor
8. tenant staging: global draft → publish → smoke render
```

بدون migration Definition.

## Module UI جدید (`news`)

```text
1. ModuleSeeder → key news
2. Enable for tenant(s)
3. section type: latest-news (PageType rules)
4. Variant news-latest-01 + requiresModules: [news]
5. NewsSectionQuery + NewsSectionDataProvider + register
6. Blade view + optional @vite JS
7. tenant-ui:doctor
8. Dashboard catalog خودکار variant را نشان می‌دهد (اگر module on)
```

این **الگوی تکرار** برای shop/stories/blog است.

## Disable Module قدیمی

Impact → حذف sections از pages → publish → سپس disable module.

→ [Module Impact](../13-module-impact-and-compatibility.md)
