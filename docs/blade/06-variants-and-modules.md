# Variantها و اتصال Module

[← فهرست Blade](README.md) · [ثبت Definition](../05-registering-definitions.md)

## Naming

- **`type`**: مفهوم ثابت — `header`, `products`, `story`, `banner`
- **`key`**: globally unique — `{owner}-{concept}-{nn}`

```text
core-header-01 · shop-products-grid-01 · stories-strip-01 · blog-latest-01
```

## Global Components

```php
$variants->register(new VariantDefinition(
    key: 'core-header-01',
    type: 'header',
    scope: VariantDefinition::SCOPE_GLOBAL,
    label: 'Header Core 01',
    bladeView: 'website.global.headers.core-header-01',
    supportedLayouts: ['full-width'],
    status: VariantDefinition::STATUS_ACTIVE,
));
```

## Section + Module — پنج قطعه

```text
1. Module key (Host)
2. VariantDefinition (requiresModules, allowedPageTypes, config/translation schema)
3. PageType section rule
4. DataProvider (scope+type)
5. Blade view
```

### Products (`shop`)

```php
$variants->register(new VariantDefinition(
    key: 'shop-products-grid-01',
    type: 'products',
    scope: VariantDefinition::SCOPE_SECTION,
    bladeView: 'website.sections.shop.products.shop-products-grid-01',
    requiresModules: ['shop'],
    allowedPageTypes: ['home', 'landing'],
    configFields: [ /* source, limit */ ],
    translationFields: [ /* title, more_text */ ],
));
```

- Module `shop` off → Catalog/Compatibility حذف
- PageType `about` → variant مجاز نیست

### Story (`stories`)

```php
requiresModules: ['stories'],
type: 'story',
key: 'stories-strip-01',
```

PageType `home` می‌گوید story **مجاز** است؛ Module System می‌گوید Tenant **دارد یا نه**.

→ [DataProvider](09-data-providers-and-queries.md)
