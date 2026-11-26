# ساختار پیشنهادی پروژه

[← فهرست Blade](README.md)

Convention اجباری نیست؛ از اول ثابت نگه دارید.

```text
app/
├── Providers/
│   └── TenantUiDefinitionServiceProvider.php
│
├── TenantUi/
│   ├── Resolvers/
│   │   ├── TenantResolver.php
│   │   ├── ModuleResolver.php
│   │   └── LocaleManager.php
│   ├── DataProviders/
│   │   ├── HeaderDataProvider.php
│   │   ├── ProductSectionDataProvider.php
│   │   └── StorySectionDataProvider.php
│   └── Queries/
│       ├── ProductSectionQuery.php
│       └── StorySectionQuery.php
│
database/seeders/
├── ModuleSeeder.php
├── TenantModuleSeeder.php
└── TenantUiBootstrapSeeder.php
│
resources/
├── views/website/
│   ├── page.blade.php
│   ├── themes/core/master.blade.php
│   ├── global/headers|footers/
│   └── sections/
│       ├── core/hero|banner/
│       ├── shop/products/
│       ├── stories/story/
│       └── blog/latest-posts/
├── css/themes/core/app.css
└── js/
    ├── themes/core/app.js
    └── modules/stories/slider.js
```

## Definitionهای زیاد

```text
app/TenantUi/Definitions/
├── Themes.php
├── GlobalVariants.php
├── ShopVariants.php
├── StoryVariants.php
├── Presets.php
└── PageTypes.php
```

Provider فقط این کلاس‌ها را `boot` می‌کند.

→ [Seeder و Bootstrap](03-seeding-bootstrap-modules.md)
