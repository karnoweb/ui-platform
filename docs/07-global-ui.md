# Global UI

[← فهرست](README.md)

## Draft

```php
$config = $ui->global()->draft()->save(
    themeKey: 'modern',
    presetKey: 'modern-shop',
    overrides: ['header' => 'header-5'],
    expectedRevision: $config->draft_revision ?? null,
);
```

- `expectedRevision` برای optimistic locking — conflict → `DraftConflictException`.
- Draft در `tenant_ui_configs` (theme_key, preset_key, overrides, draft_revision).

## Publish

```php
$version = $ui->global()->publish(publishedBy: optional user id);
```

### جریان داخلی

```text
Lock config row
→ Resolve theme + preset + overrides (TenantUiResolver)
→ CompatibilityService (variants, modules, theme)
→ PublishedReleaseValidator: همه ui_pages با published_version_id
→ Checksum payload
→ اگر تغییر نکرده: reuse version id
→ tenant_ui_versions.insert + published_version_id update
→ Cache: pointer + version snapshot (بعد از commit)
→ Event: TenantUiPublished
```

ناسازگاری با Page منتشرشده → `GlobalPublishImpactException` (`$exception->pages`).

## پیش‌نیاز Page Publish

حداقل **یک بار** Global Publish لازم است تا Page Publish بتواند با **Published Theme** validate کند.

ترتیب:

```text
Global Draft → Global Publish → Pages → Page Publish
```

## Preview vs Publish Theme

| مرحله | Theme برای Validation |
| --- | --- |
| Draft edit / Preview | Draft (`tenant_ui_configs.theme_key`) |
| Page Publish | Published Global (`PublishedGlobalConfigService`) |
| Global Publish | Draft جدید + Pages Published |

→ [Preview، Publish، Runtime](09-preview-publish-runtime.md)

## Event

`KarnoWeb\TenantUi\Events\TenantUiPublished`

→ [Exception](15-exceptions-events.md)
