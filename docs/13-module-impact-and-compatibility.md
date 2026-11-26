# Module Impact و Compatibility

[← فهرست](README.md)

## CompatibilityService

در Draft mutation و Publish بررسی می‌کند:

- Variant status
- Page type rules
- Theme layout/capabilities
- `ModuleResolverGate` → modules enabled

بدون `ModuleResolverContract` → `ConfigurationException` (fail-closed).

## Page Publish vs Preview

| Context | Theme source |
| --- | --- |
| `UiValidationContext::Draft` | `tenant_ui_configs.theme_key` |
| `UiValidationContext::Publish` | Published global snapshot |

## Global Publish Impact

`PublishedReleaseValidator` تمام pageهای `published_version_id NOT NULL` را با theme جدید compile/validate می‌کند.

فقط `CompatibilityException` catch می‌شود — خطای DB/programming propagate.

## Module Impact Analyzer

```php
$usages = $ui->impact()->moduleUsages('search');
/*
[
  'global' => [
    ['component' => 'header', 'variant' => 'header-5'],
  ],
  'pages' => [
    ['page_key' => 'home', 'section_type' => 'search', 'variant' => 'search-box-1'],
  ],
]
*/

$ui->impact()->canDisableModule('stories'); // bool
$ui->impact()->assertCanDisableModule('stories'); // or ModuleDisableImpactException
```

Scanner:

- **Published Global** components (resolved overrides)
- **Published Pages** → sections در version payload

Draft در Impact **نیست**.

## Disable Module در Host App

```text
impact()->assertCanDisableModule('x')
→ disable in ModuleManager
```

→ [Exception](15-exceptions-events.md)
