# Blade Rendering، Controller و Route

[← فهرست Blade](README.md) · [Runtime Payload](../09-preview-publish-runtime.md)

## متغیرهای Blade Section

`BladeRenderer` به View می‌دهد:

| متغیر | منبع |
| --- | --- |
| `$config` | Structural (limit, source, …) |
| `$content` | Translation locale فعلی |
| `$data` | خروجی `UiDataProviderContract` |
| `$section` | instance (type, variant, position, …) |
| `$runtime` | tenant, theme, page, locale, components |
| `$extra` | آرگومان Host به `renderGlobal` / `renderSections` |

**Blade Query نمی‌زند.**

## نمونه Products Section

```blade
<section class="products-section">
    @if (!empty($content['title']))
        <h2>{{ $content['title'] }}</h2>
    @endif
    <div class="products-grid">
        @foreach ($data['products'] ?? [] as $product)
            <article>...</article>
        @endforeach
    </div>
</section>
```

## Shell صفحه

```blade
{{-- resources/views/website/page.blade.php --}}
@extends($layout)
@section('title', $payload['page']['seo_title'] ?? $payload['page']['title'] ?? config('app.name'))
@section('content')
    {!! TenantUi::render()->renderGlobal($payload, 'header', $renderExtra ?? []) !!}
    <main>
        {!! TenantUi::render()->renderSections($payload, $renderExtra ?? []) !!}
    </main>
    {!! TenantUi::render()->renderGlobal($payload, 'footer', $renderExtra ?? []) !!}
@endsection
```

## HomeController

```php
$tenantKey = (string) app('currentTenant')->getKey();
$ui = TenantUi::for($tenantKey);
$payload = $ui->runtime()->byPageKey('home', app()->getLocale());
$layout = TenantUi::themes()->get($payload['theme'])->bladeLayout;
$renderExtra = [
    'shared' => app(SiteSharedDataService::class)->getCached($tenantKey, app()->getLocale()),
];
return view('website.page', compact('payload', 'layout', 'renderExtra'));
```

## Route Home

```php
Route::middleware(['resolve.tenant', 'set.locale'])->group(function () {
    Route::get('/', HomeController::class);
});
```

## Dynamic Page (About / Landing)

```php
$payload = TenantUi::for($tenantKey)->runtime()->bySlug($slug, app()->getLocale());
```

```php
Route::get('/{slug}', DynamicPageController::class)
    ->where('slug', '.*'); // بعد از routeهای ثابت (cart, login, …)
```

Locale prefix را Host App مدیریت کند.

## معماری Blade (خلاصه)

```text
HTTP → Tenant/Locale Middleware
→ TenantUi::for → runtime payload
→ ThemeDefinition → master layout
→ BladeRenderer → DataProvider → Domain Query → Cache/DB
```

→ [Cache و shared extra](10-caching-three-layers.md)
