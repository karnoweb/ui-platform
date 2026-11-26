# نمای کلی

[← فهرست](README.md)

## این پکیج چه مسئله‌ای را حل می‌کند؟

`karnoweb/laravel-tenant-ui` هستهٔ مدیریت UI برای SaaS چندمستاجری است — نه یک Theme جدا برای هر Tenant، بلکه **یک Backend مشترک** با تنظیمات مستقل per-tenant.

### قابلیت‌ها

- Theme، Preset، Override کامپوننت‌های Global (Header، Footer، …)
- صفحات typed (Home، Landing، …) با Page Builder محدود به Schema
- Section، Config، ترجمه چندزبانه
- **Draft → Preview → Validate → Publish → Immutable Version → Runtime**
- Payload یکپارچه برای Blade یا SPA (Next/Nuxt)
- Cache و Versioning مناسب Production

### مدل ذهنی

```text
Tenant
├── Global UI (Draft / Published Version)
│   ├── theme_key, preset_key, overrides
│   └── snapshot: theme + components
├── Pages (Draft / Published Version each)
│   ├── translations (slug per locale)
│   └── sections (ordered, config + translations)
├── Modules (از Host App — ModuleResolverContract)
└── Runtime
    ├── resolve pointers → version snapshots
    └── merge + localize → payload
```

### مرز مسئولیت

| داخل Package | خارج Package (Host App) |
| --- | --- |
| Draft/Publish، Version، Route منتشرشده | مدل Tenant، Auth، Billing |
| Compatibility با Definitionها | Business Query در DataProvider |
| Runtime Cache، Impact Analyzer | Module enable/disable واقعی |
| Registry Definition (کد) | Blade View، Theme Layout |

### معماری Draft / Published

Runtime **هرگز** Draft را نمی‌خواند. وضعیت Live فقط از:

- `tenant_ui_configs.published_version_id` (Global)
- `ui_pages.published_version_id` (Page)

تعیین می‌شود. فیلد `is_active` روی Page **Draft state** است تا Publish بعدی صفحه را Offline کند.

جزئیات: [مفاهیم هسته → Draft و Published](02-core-concepts.md#draft-و-published)

### گام بعد

- [نصب](03-installation-and-configuration.md)
- [Quick Start](18-quick-start-walkthrough.md)
