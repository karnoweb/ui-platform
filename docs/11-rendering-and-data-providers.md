# Blade و Data Provider

[← فهرست](README.md)

## اصل

Variant **Query نمی‌زند**. داده از `UiDataProviderContract` می‌آید؛ Blade فقط نمایش.

## BladeRenderer

```php
$payload = $ui->runtime()->byPageKey('home', app()->getLocale());
$theme = TenantUi::themes()->get($payload['theme']);

return view('website.page', [
    'payload' => $payload,
    'layout' => $theme->bladeLayout,
]);
```

```blade
@extends($layout)

@section('content')
    {!! TenantUi::render()->renderGlobal($payload, 'header') !!}
    {!! TenantUi::render()->renderSections($payload) !!}
    {!! TenantUi::render()->renderGlobal($payload, 'footer') !!}
@endsection
```

Renderer **دوباره** theme/page resolve نمی‌کند — فقط runtime payload + registry variant blade.

## ثبت Provider

```php
$providers->register(
    VariantDefinition::SCOPE_SECTION,
    'products',
    ProductSectionDataProvider::class,
);
```

## UiDataProviderContract

```php
public function provide(RenderContext $context, array $config): array;
```

`RenderContext`: `tenantKey`, `locale`, `pageKey`, `pageType`, `sectionInstanceKey`, `extra`.

## قرارداد Performance

Host App باید رعایت کند:

- تعداد Query **bounded** per section/global render
- **Eager loading** — بدون N+1
- **بدون query داخل loop** روی collection بزرگ
- **limit / pagination** اجباری برای لیست‌ها
- cache سطح Application برای aggregateهای سنگین (اختیاری)

Memoizer فقط duplicate call در **همان request** را حذف می‌کند.

مثال rough budget یک Home:

```text
Hero 1q + Story 2q + Products 4q + Blog 3q ≈ 10–20 query
```

## MemoizableDataProviderContract

```php
public function memoKey(RenderContext $context, array $config): string;
```

Package کلید نهایی: `{Fqcn}:{memoKey}` — collision بین Providerها ممکن نیست.

فقط وقتی implement کنید که واقعاً می‌خواهید چند section/instance داده share کنند.

## Anti-Patterns

- Query در Blade `@php`
- Business rule در VariantDefinition
- Bypass Provider و load model در view

→ [Production Checklist](17-production-checklist.md)
