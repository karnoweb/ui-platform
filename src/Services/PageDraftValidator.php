<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Illuminate\Validation\ValidationException;
use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\DTO\UiValidationContext;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Models\UiPageRoute;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;

final readonly class PageDraftValidator
{
    public function __construct(
        private PageTypeRegistry $pageTypes,
        private CompatibilityService $compatibility,
        private SchemaValidationService $schema,
        private LocaleManagerContract $locales,
        private PublishedGlobalConfigService $publishedGlobal,
    ) {
    }

    public function validate(
        UiPage $page,
        UiValidationContext $context = UiValidationContext::Draft,
    ): void {
        $page->loadMissing([
            'translations',
            'sections.translations',
        ]);

        $pageType = $this->pageTypes->get($page->type);

        $themeKey = $this->resolveThemeKey($page->tenant_key, $context);

        $fallbackLocale = $this->locales->fallback($page->tenant_key);
        $supportedLocales = $this->locales->supported($page->tenant_key);

        $fallbackPageTranslation = $page->translations
            ->firstWhere('locale', $fallbackLocale);

        if (! $fallbackPageTranslation) {
            throw ValidationException::withMessages([
                'translations' => [
                    "Fallback locale [{$fallbackLocale}] must have a page translation.",
                ],
            ]);
        }

        if (! $fallbackPageTranslation->slug) {
            throw ValidationException::withMessages([
                'translations.slug' => [
                    "Fallback locale [{$fallbackLocale}] must have a slug.",
                ],
            ]);
        }

        foreach ($page->translations as $translation) {
            if (! in_array($translation->locale, $supportedLocales, true)) {
                throw ValidationException::withMessages([
                    'translations.locale' => [
                        "Locale [{$translation->locale}] is not supported by tenant.",
                    ],
                ]);
            }

            if ($translation->slug) {
                $conflict = UiPageRoute::query()
                    ->where('tenant_key', $page->tenant_key)
                    ->where('locale', $translation->locale)
                    ->where('slug', $translation->slug)
                    ->where('page_id', '!=', $page->id)
                    ->exists();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'translations.slug' => [
                            "Published slug [{$translation->slug}] is already used.",
                        ],
                    ]);
                }
            }
        }

        $enabledSections = $page->sections
            ->where('is_enabled', true)
            ->sortBy('position')
            ->values();

        $counts = $enabledSections->countBy('type');

        foreach ($pageType->sectionRules as $type => $rule) {
            $count = (int) ($counts[$type] ?? 0);
            $min = $rule['min'] ?? 0;
            $max = $rule['max'] ?? null;

            if ($count < $min) {
                throw ValidationException::withMessages([
                    'sections' => [
                        "Section type [{$type}] requires at least [{$min}] item(s).",
                    ],
                ]);
            }

            if ($max !== null && $count > $max) {
                throw ValidationException::withMessages([
                    'sections' => [
                        "Section type [{$type}] allows at most [{$max}] item(s).",
                    ],
                ]);
            }
        }

        foreach ($enabledSections as $section) {
            if (! $pageType->allows($section->type)) {
                throw ValidationException::withMessages([
                    'sections' => [
                        "Section type [{$section->type}] is not allowed on [{$page->type}].",
                    ],
                ]);
            }

            $variant = $this->compatibility->assertVariant(
                tenantKey: $page->tenant_key,
                themeKey: $themeKey,
                variantKey: $section->variant_key,
                expectedScope: VariantDefinition::SCOPE_SECTION,
                expectedType: $section->type,
                pageType: $page->type,
            );

            $this->schema->validate(
                (array) ($section->config ?? []),
                $variant->configFields,
            );

            foreach ($section->translations as $translation) {
                if (! in_array($translation->locale, $supportedLocales, true)) {
                    throw ValidationException::withMessages([
                        'sections.translations.locale' => [
                            "Locale [{$translation->locale}] is not supported by tenant.",
                        ],
                    ]);
                }

                $this->schema->validate(
                    (array) ($translation->content ?? []),
                    $variant->translationFields,
                );
            }
        }
    }

    private function resolveThemeKey(
        string $tenantKey,
        UiValidationContext $context,
    ): string {
        if ($context === UiValidationContext::Publish) {
            return $this->publishedGlobal->themeKey($tenantKey);
        }

        /** @var TenantUiConfig $globalConfig */
        $globalConfig = TenantUiConfig::query()
            ->where('tenant_key', $tenantKey)
            ->firstOrFail();

        return $globalConfig->theme_key;
    }
}
