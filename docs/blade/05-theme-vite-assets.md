# Theme Core، Vite و Asset

[← فهرست Blade](README.md)

## ثبت Theme Core (کد — نه Seeder)

```php
$themes->register(new ThemeDefinition(
    key: 'core',
    label: 'Core',
    layout: 'full-width',
    capabilities: ['sticky-header', 'wide-container'],
    bladeLayout: 'website.themes.core.master',
    meta: ['preview_image' => '/images/themes/core-preview.webp'],
));
```

Provider در `bootstrap/providers.php` ثبت شود.

## Package Asset Manager نیست

> هر Theme یک **Vite entry** + **master layout** که `@vite` را load می‌کند.

```text
resources/css/themes/core/app.css
resources/js/themes/core/app.js   → import css
```

## vite.config.js

```js
laravel({
    input: [
        'resources/js/app.js',
        'resources/js/themes/core/app.js',
        'resources/js/modules/stories/slider.js', // optional heavy
    ],
});
```

Theme بعدی: `resources/js/themes/minimal/app.js` → input جدید.

## Master Layout

```blade
{{-- resources/views/website/themes/core/master.blade.php --}}
@vite('resources/js/themes/core/app.js')
@stack('styles')
@yield('content')
@stack('scripts')
```

Registry فقط `bladeLayout` را می‌دهد — **مسیر CSS/JS در Layout** است، نه DB.

## JS/CSS مخصوص Variant

Slider فقط وقتی Section Story هست:

```blade
@once
    @push('scripts')
        @vite('resources/js/modules/stories/slider.js')
    @endpush
@endonce
```

قاعده:

| Asset | کجا |
| --- | --- |
| Theme-wide | master `@vite` |
| کوچک | theme bundle |
| سنگین/optional | Variant `@once` + `@push` |

→ [Variant](06-variants-and-modules.md)

## اضافه کردن Theme جدید (`minimal`)

```text
1. master Blade + css/js entry
2. Vite input
3. ThemeDefinition register
4. Variantهای compatible (یا reuse)
5. Preset جدید
6. php artisan tenant-ui:doctor
7. staging tenant test
```

بدون migration برای Definition.

→ [گسترش Theme](13-extending-theme-module.md)
