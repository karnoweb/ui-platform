# ثبت Definitionها

[← فهرست](README.md)

Definitionها در **Registry در-memory** (PHP) ثبت می‌شوند — نه در DB. دلیل: [Database → چرا Definition در DB نیست](14-database.md#چرا-definition-در-database-نیست).

## Theme

```php
use KarnoWeb\TenantUi\Definitions\ThemeDefinition;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;

$themes->register(new ThemeDefinition(
    key: 'modern',
    label: 'Modern',
    layout: 'full-width',
    capabilities: ['sticky-header', 'wide-container'],
    bladeLayout: 'website.themes.modern.master',
    meta: ['preview_image' => '/images/themes/modern.jpg'],
));
```

## Global Variant

```php
use KarnoWeb\TenantUi\Definitions\VariantDefinition;

$variants->register(new VariantDefinition(
    key: 'header-5',
    type: 'header',
    scope: VariantDefinition::SCOPE_GLOBAL,
    label: 'Header 5',
    bladeView: 'website.components.headers.header-5',
    requiresModules: ['search'],
    optionalModules: ['cart', 'auth'],
    supportedLayouts: ['full-width', 'boxed'],
    requiredThemeCapabilities: ['sticky-header'],
    status: VariantDefinition::STATUS_ACTIVE,
));
```

## Section Variant

```php
$variants->register(new VariantDefinition(
    key: 'story-2',
    type: 'story',
    scope: VariantDefinition::SCOPE_SECTION,
    label: 'Story 2',
    bladeView: 'website.sections.story.story-2',
    requiresModules: ['stories'],
    allowedPageTypes: ['home'],
    configFields: [
        [
            'key' => 'limit',
            'type' => 'number',
            'label' => 'تعداد',
            'rules' => ['nullable', 'integer', 'min:1', 'max:30'],
            'default' => 10,
        ],
    ],
    translationFields: [
        [
            'key' => 'title',
            'type' => 'text',
            'label' => 'عنوان',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
    ],
));
```

### وضعیت Variant

| Status | رفتار |
| --- | --- |
| `active` | انتخاب جدید در Catalog |
| `deprecated` | موجود OK؛ انتخاب جدید مخفی |
| `disabled` | Compatibility رد |

Variant در Published استفاده‌شده را **حذف نکنید** — deprecate کنید.

## Preset

```php
use KarnoWeb\TenantUi\Definitions\PresetDefinition;

$presets->register(new PresetDefinition(
    key: 'modern-shop',
    label: 'Modern Shop',
    components: [
        'header' => 'header-5',
        'footer' => 'footer-2',
        'product-card' => 'product-card-4',
    ],
    supportedThemes: ['modern'],
));
```

## Page Type

```php
use KarnoWeb\TenantUi\Definitions\PageTypeDefinition;

$pageTypes->register(new PageTypeDefinition(
    key: 'home',
    label: 'Home',
    sectionRules: [
        'hero' => ['min' => 0, 'max' => 1],
        'story' => ['min' => 0, 'max' => 1],
        'products' => ['min' => 0, 'max' => null],
        'banner' => ['min' => 0, 'max' => null],
    ],
));
```

`max => null` = بدون سقف تعداد.

## Data Provider Registry

```php
$providers->register(
    VariantDefinition::SCOPE_SECTION,
    'products',
    ProductSectionDataProvider::class,
);
```

کلید: `{scope}:{type}` — یک Provider per type در آن scope.

## اسکلت Service Provider

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use KarnoWeb\TenantUi\Registries\DataProviderRegistry;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final class TenantUiDefinitionServiceProvider extends ServiceProvider
{
    public function boot(
        ThemeRegistry $themes,
        PresetRegistry $presets,
        VariantRegistry $variants,
        PageTypeRegistry $pageTypes,
        DataProviderRegistry $providers,
    ): void {
        // register definitions...
    }
}
```

Duplicate key در Registry → exception.

→ [Blade و Data Provider](11-rendering-and-data-providers.md)
