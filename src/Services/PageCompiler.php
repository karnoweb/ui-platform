<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final readonly class PageCompiler
{
    public function __construct(
        private VariantRegistry $variants,
        private SchemaValidationService $schema,
    ) {
    }

    public function compile(UiPage $page): array
    {
        $page->loadMissing([
            'translations',
            'sections.translations',
        ]);

        $pageTranslations = [];

        foreach ($page->translations as $translation) {
            $pageTranslations[$translation->locale] = [
                'title' => $translation->title,
                'slug' => $translation->slug,
                'seo_title' => $translation->seo_title,
                'seo_description' => $translation->seo_description,
                'extra' => $translation->extra ?? [],
            ];
        }

        $sections = [];

        foreach (
            $page->sections
                ->where('is_enabled', true)
                ->sortBy('position')
                ->values()
            as $section
        ) {
            $variant = $this->variants->get($section->variant_key);

            $translations = [];

            foreach ($section->translations as $translation) {
                $translations[$translation->locale] =
                    $this->schema->validate(
                        (array) ($translation->content ?? []),
                        $variant->translationFields,
                    );
            }

            $sections[] = [
                'instance_key' => $section->instance_key,
                'type' => $section->type,
                'variant' => $section->variant_key,
                'position' => $section->position,
                'config' => $this->schema->validate(
                    (array) ($section->config ?? []),
                    $variant->configFields,
                ),
                'translations' => $translations,
            ];
        }

        return [
            'page' => [
                'id' => $page->id,
                'key' => $page->page_key,
                'type' => $page->type,
                'translations' => $pageTranslations,
            ],
            'sections' => $sections,
        ];
    }
}
