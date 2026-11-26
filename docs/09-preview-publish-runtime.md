# Preview، Publish، Runtime

[← فهرست](README.md)

## Preview (Draft)

```php
$payload = $ui->preview(pageId: $page->id, locale: 'fa');
```

- از Draft Global Theme + Draft Page می‌سازد.
- برای Dashboard / iframe preview.

## Publish Page

```php
$version = $ui->pages()->publish(
    pageId: $page->id,
    publishedBy: (string) auth()->id(),
);
```

### جریان

```text
lock page
→ اگر !is_active: routes حذف، published_version_id=null، return null
→ PageDraftValidator (UiValidationContext::Publish)
→ PageCompiler → checksum
→ ui_page_versions + published_version_id
→ ui_page_routes sync (DB transaction)
→ after commit: route cache + pointer + version cache
→ PagePublished event
```

Validation: Published Global Theme، min/max sections، modules، schema، locale/slug rules.

## Runtime

```php
$payload = $ui->runtime()->byPageKey('home', 'fa');
$payload = $ui->runtime()->bySlug('about-us', 'en');
```

Runtime **فقط** `published_version_id IS NOT NULL` — فیلتر `is_active` در query Runtime **نیست**.

### نمونه Payload

```json
{
  "tenant": "tenant-25",
  "locale": "fa",
  "fallback_locale": "fa",
  "global_version": 4,
  "page_version": 18,
  "theme": "modern",
  "preset": "modern-shop",
  "components": { "header": "header-5", "footer": "footer-2" },
  "page": {
    "key": "home",
    "type": "home",
    "title": "صفحه اصلی",
    "slug": "/",
    "seo_title": null,
    "seo_description": null,
    "extra": {}
  },
  "sections": [
    {
      "instance_key": "uuid",
      "type": "products",
      "variant": "products-3",
      "position": 1,
      "config": { "limit": 12 },
      "content": { "title": "پرفروش‌ترین‌ها" }
    }
  ]
}
```

### Localization

```text
requested locale → translation
              ↘ fallback locale → translation
              ↘ empty content
```

### ETag (HTTP)

```text
global-{global_version}-page-{page_version}-{locale}-{fallback_locale}
```

→ [Runtime HTTP API](12-runtime-http-api.md)

## سناریو: Draft inactive

```text
Page Live
→ Draft: setActive(false)  // هنوز Live
→ Publish                   // offline
```

Pointer cache expire قبل از Publish **نباید** 404 دهد — Runtime از published_version_id می‌خواند نه is_active.

→ [Cache](10-cache-and-performance.md)
