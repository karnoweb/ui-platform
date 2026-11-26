# Database

[← فهرست](README.md)

## جداول

### `tenant_ui_configs`

| Column | توضیح |
| --- | --- |
| `tenant_key` | unique |
| `theme_key`, `preset_key`, `overrides` | **Draft** Global |
| `draft_revision` | optimistic lock |
| `published_version_id` | FK منطقی به `tenant_ui_versions` |

### `tenant_ui_versions`

Immutable Global snapshot: `payload` JSON, `checksum`, `version`, `published_at`.

### `ui_pages`

| Column | توضیح |
| --- | --- |
| `tenant_key`, `page_key` | unique together |
| `type` | PageType key |
| `is_active` | **Draft** — offline واقعی بعد از Publish |
| `draft_revision` | |
| `published_version_id` | |

### `ui_page_translations`

Draft content per locale (title, slug, seo, extra JSON).

### `ui_page_sections`

Draft sections: `instance_key`, `type`, `variant_key`, `position`, `config`, `is_enabled`.

### `ui_page_section_translations`

Draft section content per locale.

### `ui_page_versions`

Immutable page snapshot (`payload` شامل compiled page + sections).

### `ui_page_routes`

**Published only:** `tenant_key`, `locale`, `slug` unique — برای `bySlug()`.

## روابط (خلاصه)

```text
tenant_ui_configs ──published_version_id──► tenant_ui_versions
ui_pages ──published_version_id──► ui_page_versions
ui_pages ──has many──► ui_page_sections, translations
ui_page_versions ──has many──► ui_page_routes (on publish sync)
```

## چرا Definition در Database نیست؟

| Code Registry | DB Tenant Data |
| --- | --- |
| Version deploy با Git | Draft per tenant |
| Review در PR | ویرایش Dashboard |
| Type-safe PHP | JSON payload versions |
| Catalog ثابت کل سیستم | Snapshot immutable |

تغییر Variant در کد + Tenant قدیمی Published → lifecycle `deprecated` نه delete.

## Migration

مسیر: `database/migrations/` در package — `loadMigrationsFrom`.

→ [Exception](15-exceptions-events.md)
