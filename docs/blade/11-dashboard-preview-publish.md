# Dashboard، Preview و Publish

[← فهرست Blade](README.md) · [Page Builder](../08-page-builder.md) · [Dashboard Core](../16-dashboard-and-operations.md)

## صفحات Dashboard (حداقل)

```text
Appearance — Theme/Preset/Header/Footer + Publish Global
Pages — لیست home, about, landing, …
Page Builder — metadata, translations, sections, preview, publish
```

## Catalog (Backend-driven UI)

```php
$ui = TenantUi::for($tenantKey);
$catalog = $ui->catalog();

$variants = $catalog->variantsFor(
    tenantKey: $ui->key(),
    themeKey: 'core', // یا draft theme در editor
    scope: VariantDefinition::SCOPE_SECTION,
    type: 'products',
    pageType: 'home',
);
```

Catalog خودش فیلتر می‌کند:

- Module disabled
- Theme incompatible
- PageType incompatible

Frontend **حدس نزند** — لیست را از API backend بگیرد.

## Draft Revision

هر mutation → `draft_revision++`.  
Request body شامل `expectedRevision`؛ conflict → `DraftConflictException` → UI: reload + پیام.

## Preview

```php
$payload = $ui->preview(pageId: $id, locale: 'fa');
```

- **Draft theme** — نه Published
- Route preview **authenticated** (iframe Dashboard) — public نباشد

## Publish Flow Tenant جدید

```text
Modules setup (Seeder/Admin)
→ Global Draft → Global Publish
→ Create Pages → Draft sections
→ Preview → Page Publish
```

Page **قبل از** اولین Global Publish نباید publish شود.

## Disable Module

```php
$ui->impact()->assertCanDisableModule('stories');
// سپس disable در ModuleManager
```

اگر section published دارد → `ModuleDisableImpactException` — ابتدا section را حذف + page publish.

→ [Module Impact](../13-module-impact-and-compatibility.md)
