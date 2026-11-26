# Quick Start — راه‌اندازی یک Tenant

[← فهرست](README.md)

فرض: Definitionها و Resolverها در Host App ثبت شده‌اند.

## 1. Global Draft + Publish

```php
use KarnoWeb\TenantUi\Facades\TenantUi;

$ui = TenantUi::for('tenant-acme');

$config = $ui->global()->draft()->save(
    themeKey: 'modern',
    presetKey: 'modern-shop',
    overrides: ['header' => 'header-5'],
    expectedRevision: null,
);

$ui->global()->publish();
```

## 2. ساخت Home

```php
$pages = $ui->pages();
$page = $pages->createPage('home', 'home');

$pages->upsertPageTranslation(
    pageId: $page->id,
    locale: 'fa',
    data: ['title' => 'خانه', 'slug' => '/'],
    expectedRevision: $page->draft_revision,
);

$page = $page->fresh();

$section = $pages->addSection(
    pageId: $page->id,
    type: 'hero',
    variantKey: 'hero-2',
    config: [],
    expectedRevision: $page->draft_revision,
);

$page = $page->fresh();

$pages->upsertSectionTranslation(
    sectionId: $section->id,
    locale: 'fa',
    content: ['title' => 'به فروشگاه ما خوش آمدید'],
    expectedRevision: $page->draft_revision,
);
```

## 3. Preview و Publish

```php
$preview = $ui->preview($page->id, 'fa');

$ui->pages()->publish($page->id);
```

## 4. Runtime

```php
$live = $ui->runtime()->byPageKey('home', 'fa');
```

## 5. Blade (اختیاری)

```php
$payload = $ui->runtime()->byPageKey('home', app()->getLocale());
$layout = TenantUi::themes()->get($payload['theme'])->bladeLayout;

return view('website.page', compact('payload', 'layout'));
```

## 6. Module guard

```php
$ui->impact()->assertCanDisableModule('stories');
```

---

مراحل بعدی:

- [Page Builder کامل](08-page-builder.md)
- [Cache Production](10-cache-and-performance.md)
- [Dashboard](16-dashboard-and-operations.md)
