# Dashboard و عملیات

[← فهرست](README.md)

## ساختار پیشنهادی Dashboard

```text
Tenant UI Settings
├── Global
│   ├── Theme / Preset picker (catalog)
│   ├── Component overrides
│   ├── Preview global
│   └── Publish global
├── Pages list
│   └── Page Builder
│       ├── Metadata + translations
│       ├── Section list (reorder)
│       ├── Add section (filtered catalog)
│       ├── Preview page
│       └── Publish page
└── Modules (Host App)
    └── before disable → impact()->moduleUsages()
```

## Catalog API

```php
$catalog = $ui->catalog();
// themes, presets, variants (filtered by theme/page type), page types
```

برای picker: فقط `active` variants؛ deprecated برای existing selections.

## Revision در UI

- هر mutation response شامل `draft_revision` جدید.
- یا polling `UiPage` / config بعد از save.
- Publish button disabled تا client revision sync.

## Doctor Command

```bash
php artisan tenant-ui:doctor
```

بررسی‌های typical: config، resolver bindings، registry non-empty (در host).

## تست Package

```bash
composer test
# یا vendor/bin/phpunit
```

- Unit/Feature: SQLite `:memory:` + array cache.
- Integration: `TENANT_UI_INTEGRATION=1` + Redis.

→ [Quick Start](18-quick-start-walkthrough.md)

## جریان Page Builder (UX)

```text
1. Ensure global published
2. createPage
3. upsert translations (fallback slug!)
4. add sections from catalog
5. upsert section translations
6. reorder
7. preview (draft theme)
8. publish (published theme validate)
9. runtime smoke test
```

→ [Page Builder](08-page-builder.md)
