# مفاهیم هسته

[← فهرست](README.md)

## Theme

**Theme** زبان طراحی کلی (layout، capability، Blade layout master) است.

```text
modern | boxed | minimal
```

- در `ThemeDefinition` ثبت می‌شود (کد، نه DB).
- Module یا ترتیب Section را کنترل **نمی‌کند**.
- Variantها می‌توانند `requiredThemeCapabilities` داشته باشند.

## Variant

**Variant** = یک گزینهٔ قابل انتخاب برای یک نوع کامپوننت.

| Scope | مثال | کاربرد |
| --- | --- | --- |
| `global` | `header-5`, `footer-2` | Header/Footer/Product card |
| `section` | `hero-2`, `products-3` | بلوک‌های Page Builder |

Requirementهای رایج روی `VariantDefinition`:

- `requiresModules` / `optionalModules`
- `supportedLayouts`, `requiredThemeCapabilities`
- `allowedPageTypes` (برای section)
- `configFields`, `translationFields` (Schema Dashboard)
- `status`: `active` | `deprecated` | `disabled`

## Preset

**Preset** = bundle از Variantهای Global.

```text
modern-shop → header-5, footer-2, product-card-4
```

ترتیب Resolution در Global UI:

```text
Tenant overrides  →  Preset  →  config defaults
```

## Page Type

**Page Type** قوانین Section را تعریف می‌کند:

```text
home: hero max=1, products max=null, ...
```

Page Builder نمی‌تواند Section خارج از Rule اضافه کند.

## Page و Section

- **`page_key`**: شناسه پایدار داخلی (مستقل از locale).
- **Translation**: `title`, `slug`, SEO، `extra` per locale.
- **Section**: `instance_key`, `type`, `variant`, `position`, `is_enabled`, `config`, translations.

Slug منتشرشده در `ui_page_routes` با uniqueness `(tenant_key, locale, slug)`.

## Draft و Published

```text
Edit Draft → Preview (Draft Theme) → Publish → Immutable Version → Runtime (Published only)
```

| لایه | Draft | Published |
| --- | --- | --- |
| Global | `tenant_ui_configs.*` + `draft_revision` | `tenant_ui_versions` + `published_version_id` |
| Page | `ui_pages`, sections, translations | `ui_page_versions` + routes |

**Page Publish** Sectionها را با **Theme منتشرشده Global** validate می‌کند (نه Draft Theme).

**Global Publish** همه Pageهای Published را با Theme جدید دوباره بررسی می‌کند.

**Atomic Release** (Theme + چند Page یکجا): در نسخه فعلی **نیست** — [Production Checklist](17-production-checklist.md#محدودیت-atomic-release).

## Module

Module کلید منطقی (مثل `stories`, `search`) است که Host App از طریق `ModuleResolverContract::enabled()` جواب می‌دهد. Variant بدون Module فعال در Compatibility رد می‌شود (یا اگر Resolver bind نشده باشد `ConfigurationException`).

→ [Module Impact](13-module-impact-and-compatibility.md)
