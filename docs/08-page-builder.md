# Page Builder

[← فهرست](README.md)

API از `$ui->pages()` (`TenantPageBuilder`) — همه mutationها `expectedRevision` می‌خواهند.

## ساخت Page

```php
$page = $ui->pages()->createPage(
    pageKey: 'home',
    type: 'home',
    isActive: true,
);
```

`page_key` per tenant یکتا است.

## Draft Revision

هر mutation `draft_revision` را افزایش می‌دهد. بعد از هر درخواست:

```php
$page = $page->fresh();
// از $page->draft_revision در call بعدی استفاده کنید
```

Conflict → `DraftConflictException`.

## Translation صفحه

```php
$ui->pages()->upsertPageTranslation(
    pageId: $page->id,
    locale: 'fa',
    data: [
        'title' => 'خانه',
        'slug' => '/',
        'seo_title' => null,
        'seo_description' => null,
        'extra' => [],
    ],
    expectedRevision: $page->draft_revision,
);
```

Unique slug فقط روی **Published route** (`tenant_key + locale + slug`).

## Fallback Locale

قبل از Publish: Translation **fallback locale** باید وجود داشته باشد و **slug** داشته باشد (معمولاً `fa`).

## Section

```php
$section = $ui->pages()->addSection(
    pageId: $page->id,
    type: 'story',
    variantKey: 'story-2',
    config: ['limit' => 10],
    expectedRevision: $page->draft_revision,
);
```

Validate: PageType rules، variant scope=section، modules، **draft theme** compatibility، config schema.

```php
$ui->pages()->upsertSectionTranslation(
    sectionId: $section->id,
    locale: 'fa',
    content: ['title' => '...'],
    expectedRevision: $page->fresh()->draft_revision,
);

$ui->pages()->updateSection(
    sectionId: $section->id,
    variantKey: 'story-2',
    config: [...],
    isEnabled: true,
    expectedRevision: $page->draft_revision,
);

$ui->pages()->reorder(
    pageId: $page->id,
    instanceKeys: ['uuid-1', 'uuid-2'],
    expectedRevision: $page->draft_revision,
);

$ui->pages()->deleteSection($section->id, $page->draft_revision);
```

## غیرفعال کردن Page (Draft)

```php
$ui->pages()->setActive($page->id, false, $page->draft_revision);
$ui->pages()->publish($page->id); // offline واقعی
```

تا Publish نکنید، Runtime همچنان نسخه قبلی را نشان می‌دهد.

→ [Preview و Publish](09-preview-publish-runtime.md)

## Dashboard Page Builder Flow

```text
Load page + draft_revision
→ Edit translation/sections (هر پاسخ revision جدید)
→ Preview
→ Publish (validate published theme)
```

→ [Dashboard](16-dashboard-and-operations.md)
