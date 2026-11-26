# DataProvider و Query Service

[← فهرست Blade](README.md) · [Rendering Core](../11-rendering-and-data-providers.md)

## نقش DataProvider

Adapter بین **UI Platform** و **Domain**:

```text
products section → ProductSectionDataProvider → ProductSectionQuery → Product
```

## Query را در Provider تکرار نکنید

`ProductSectionQuery` reusable برای:

- Blade DataProvider
- REST API catalog
- Job / Assembler

## ProductSectionQuery (نمونه)

```php
final class ProductSectionQuery
{
    public function get(string $tenantKey, string $locale, array $config)
    {
        $limit = min((int) ($config['limit'] ?? 12), 24);
        $query = Product::query()->where('tenant_key', $tenantKey)->with(['media']);
        match ($config['source'] ?? 'latest') {
            'best_sellers' => $query->orderByDesc('sales_count'),
            'featured' => $query->where('is_featured', true),
            default => $query->latest(),
        };
        return $query->limit($limit)->get();
    }
}
```

## ProductSectionDataProvider

```php
final readonly class ProductSectionDataProvider implements UiDataProviderContract
{
    public function __construct(private ProductSectionQuery $query) {}

    public function provide(RenderContext $context, array $config): array
    {
        return [
            'products' => $this->query->get($context->tenantKey, $context->locale, $config),
        ];
    }
}
```

## Data Contract

همه variantهای `type=products` همان shape `$data` — فقط Blade/layout فرق می‌کند.

❌ Provider جدا per variant key  
✅ Provider per **section type**

## MemoizableDataProviderContract

وقتی چند section با config یکسان باید **یک query** بزنند:

```php
public function memoKey(RenderContext $context, array $config): string
{
    return sha1(json_encode([
        'tenant' => $context->tenantKey,
        'locale' => $context->locale,
        'config' => $config,
    ], JSON_THROW_ON_ERROR));
}
```

Package prefix: `{ProviderClass}:{memoKey}`.

## Query Budget (Home)

| بخش | هدف |
| --- | --- |
| Runtime Core (warm) | 0 DB |
| Header/menu | 0–1 یا cache |
| Hero | 0–1 |
| Products | 1–3 + eager |
| Stories/Blog | 1–2 each |

بدون query داخل `foreach` روی catalog.

→ [Cache Business](10-caching-three-layers.md)
