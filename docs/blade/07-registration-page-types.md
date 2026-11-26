# Registration، Preset و PageType

[← فهرست Blade](README.md)

## PageType — Home

```php
$pageTypes->register(new PageTypeDefinition(
    key: 'home',
    label: 'صفحه اصلی',
    sectionRules: [
        'hero' => ['min' => 0, 'max' => 1],
        'story' => ['min' => 0, 'max' => 1],
        'products' => ['min' => 0, 'max' => null],
        'banner' => ['min' => 0, 'max' => null],
        'latest-posts' => ['min' => 0, 'max' => null],
    ],
));
```

## About / Landing

- **about**: hero, rich-content, image-text, statistics, team
- **landing**: hero, products, story, banner, rich-content — `page_key` مثل `black-friday-2026` با `type: landing`

## Preset

فقط **Global components**:

```php
$presets->register(new PresetDefinition(
    key: 'core-default',
    label: 'Core Default',
    supportedThemes: ['core'],
    components: [
        'header' => 'core-header-01',
        'footer' => 'core-footer-01',
    ],
));
```

## Provider کامل — checklist register

```text
✓ Theme core
✓ Global variants (header, footer)
✓ Section variants (core, shop, stories, blog)
✓ Preset core-default
✓ PageTypes (home, about, contact, landing)
✓ DataProviders per section type
```

## ثبت DataProvider

```php
$providers->register(
    VariantDefinition::SCOPE_SECTION,
    'products',
    ProductSectionDataProvider::class,
);
```

همه variantهای `type=products` **یک Provider** — Data Contract یکسان:

```php
['products' => Collection|array]
```

→ [Query و Provider](09-data-providers-and-queries.md)
