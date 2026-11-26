<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Exceptions\DraftConflictException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Models\UiPageSection;
use KarnoWeb\TenantUi\Models\UiPageSectionTranslation;
use KarnoWeb\TenantUi\Models\UiPageTranslation;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final readonly class PageDraftService
{
    public function __construct(
        private PageTypeRegistry $pageTypes,
        private VariantRegistry $variants,
        private CompatibilityService $compatibility,
        private SchemaValidationService $schema,
    ) {
    }

    public function createPage(
        string $tenantKey,
        string $pageKey,
        string $type,
        bool $isActive = true,
    ): UiPage {
        $this->pageTypes->get($type);

        return UiPage::query()->create([
            'tenant_key' => $tenantKey,
            'page_key' => $pageKey,
            'type' => $type,
            'is_active' => $isActive,
            'draft_revision' => 1,
        ]);
    }

    public function setActive(
        string $tenantKey,
        int $pageId,
        bool $isActive,
        int $expectedRevision,
    ): UiPage {
        return DB::transaction(function () use (
            $tenantKey,
            $pageId,
            $isActive,
            $expectedRevision,
        ): UiPage {
            $page = $this->lockPage($tenantKey, $pageId, $expectedRevision);

            $page->is_active = $isActive;
            $page->draft_revision++;
            $page->save();

            return $page->fresh();
        });
    }

    public function upsertPageTranslation(
        string $tenantKey,
        int $pageId,
        string $locale,
        array $data,
        int $expectedRevision,
    ): UiPage {
        return DB::transaction(function () use (
            $tenantKey,
            $pageId,
            $locale,
            $data,
            $expectedRevision,
        ): UiPage {
            $page = $this->lockPage($tenantKey, $pageId, $expectedRevision);

            UiPageTranslation::query()->updateOrCreate(
                [
                    'page_id' => $page->id,
                    'locale' => $locale,
                ],
                [
                    'tenant_key' => $page->tenant_key,
                    'title' => $data['title'],
                    'slug' => $data['slug'] ?? null,
                    'seo_title' => $data['seo_title'] ?? null,
                    'seo_description' => $data['seo_description'] ?? null,
                    'extra' => $data['extra'] ?? [],
                ],
            );

            $this->touchDraft($page);

            return $page->fresh(['translations']);
        });
    }

    public function addSection(
        string $tenantKey,
        int $pageId,
        string $type,
        string $variantKey,
        array $config,
        int $expectedRevision,
    ): UiPageSection {
        return DB::transaction(function () use (
            $tenantKey,
            $pageId,
            $type,
            $variantKey,
            $config,
            $expectedRevision,
        ): UiPageSection {
            $page = $this->lockPage($tenantKey, $pageId, $expectedRevision);

            $variant = $this->assertSectionSelection(
                $page,
                $type,
                $variantKey,
            );

            $validatedConfig = $this->schema->validate(
                $config,
                $variant->configFields,
            );

            $position = ((int) UiPageSection::query()
                ->where('page_id', $page->id)
                ->max('position')) + 1;

            $section = UiPageSection::query()->create([
                'page_id' => $page->id,
                'instance_key' => (string) Str::uuid(),
                'type' => $type,
                'variant_key' => $variantKey,
                'position' => $position,
                'is_enabled' => true,
                'config' => $validatedConfig,
            ]);

            $this->touchDraft($page);

            return $section;
        });
    }

    public function updateSection(
        string $tenantKey,
        int $sectionId,
        string $variantKey,
        array $config,
        bool $isEnabled,
        int $expectedRevision,
    ): UiPageSection {
        return DB::transaction(function () use (
            $tenantKey,
            $sectionId,
            $variantKey,
            $config,
            $isEnabled,
            $expectedRevision,
        ): UiPageSection {
            [$page, $section] = $this->lockSection(
                $tenantKey,
                $sectionId,
                $expectedRevision,
            );

            $variant = $this->assertSectionSelection(
                $page,
                $section->type,
                $variantKey,
            );

            $section->variant_key = $variantKey;
            $section->config = $this->schema->validate(
                $config,
                $variant->configFields,
            );
            $section->is_enabled = $isEnabled;
            $section->save();

            $this->touchDraft($page);

            return $section->fresh();
        });
    }

    public function upsertSectionTranslation(
        string $tenantKey,
        int $sectionId,
        string $locale,
        array $content,
        int $expectedRevision,
    ): UiPageSectionTranslation {
        return DB::transaction(function () use (
            $tenantKey,
            $sectionId,
            $locale,
            $content,
            $expectedRevision,
        ): UiPageSectionTranslation {
            [$page, $section] = $this->lockSection(
                $tenantKey,
                $sectionId,
                $expectedRevision,
            );

            $variant = $this->variants->get($section->variant_key);

            $validated = $this->schema->validate(
                $content,
                $variant->translationFields,
            );

            $translation = UiPageSectionTranslation::query()->updateOrCreate(
                [
                    'section_id' => $section->id,
                    'locale' => $locale,
                ],
                [
                    'content' => $validated,
                ],
            );

            $this->touchDraft($page);

            return $translation;
        });
    }

    public function reorder(
        string $tenantKey,
        int $pageId,
        array $instanceKeys,
        int $expectedRevision,
    ): UiPage {
        return DB::transaction(function () use (
            $tenantKey,
            $pageId,
            $instanceKeys,
            $expectedRevision,
        ): UiPage {
            $page = $this->lockPage($tenantKey, $pageId, $expectedRevision);

            $sections = UiPageSection::query()
                ->where('page_id', $page->id)
                ->get()
                ->keyBy('instance_key');

            if (
                count($instanceKeys) !== $sections->count()
                || count(array_unique($instanceKeys)) !== count($instanceKeys)
            ) {
                throw ValidationException::withMessages([
                    'sections' => ['Reorder list must contain every section exactly once.'],
                ]);
            }

            foreach ($instanceKeys as $index => $instanceKey) {
                $section = $sections->get($instanceKey);

                if (! $section) {
                    throw ValidationException::withMessages([
                        'sections' => [
                            "Unknown section instance [{$instanceKey}].",
                        ],
                    ]);
                }

                $section->position = $index + 1;
                $section->save();
            }

            $this->touchDraft($page);

            return $page->fresh(['sections']);
        });
    }

    public function deleteSection(
        string $tenantKey,
        int $sectionId,
        int $expectedRevision,
    ): void {
        DB::transaction(function () use (
            $tenantKey,
            $sectionId,
            $expectedRevision,
        ): void {
            [$page, $section] = $this->lockSection(
                $tenantKey,
                $sectionId,
                $expectedRevision,
            );

            $section->delete();

            $remaining = UiPageSection::query()
                ->where('page_id', $page->id)
                ->orderBy('position')
                ->get();

            foreach ($remaining as $index => $item) {
                $item->forceFill([
                    'position' => $index + 1,
                ])->save();
            }

            $this->touchDraft($page);
        });
    }

    private function assertSectionSelection(
        UiPage $page,
        string $type,
        string $variantKey,
    ): VariantDefinition {
        $pageType = $this->pageTypes->get($page->type);

        if (! $pageType->allows($type)) {
            throw ValidationException::withMessages([
                'type' => [
                    "Section type [{$type}] is not allowed on page type [{$page->type}].",
                ],
            ]);
        }

        $config = TenantUiConfig::query()
            ->where('tenant_key', $page->tenant_key)
            ->firstOrFail();

        return $this->compatibility->assertVariant(
            tenantKey: $page->tenant_key,
            themeKey: $config->theme_key,
            variantKey: $variantKey,
            expectedScope: VariantDefinition::SCOPE_SECTION,
            expectedType: $type,
            pageType: $page->type,
        );
    }

    private function lockPage(
        string $tenantKey,
        int $pageId,
        int $expectedRevision,
    ): UiPage {
        /** @var UiPage $page */
        $page = UiPage::query()
            ->where('tenant_key', $tenantKey)
            ->whereKey($pageId)
            ->lockForUpdate()
            ->firstOrFail();

        if ($page->draft_revision !== $expectedRevision) {
            throw DraftConflictException::revision(
                $expectedRevision,
                $page->draft_revision,
            );
        }

        return $page;
    }

    /**
     * @return array{0: UiPage, 1: UiPageSection}
     */
    private function lockSection(
        string $tenantKey,
        int $sectionId,
        int $expectedRevision,
    ): array {
        /** @var UiPageSection $section */
        $section = UiPageSection::query()->findOrFail($sectionId);

        $page = $this->lockPage(
            $tenantKey,
            (int) $section->page_id,
            $expectedRevision,
        );

        if ((int) $section->page_id !== (int) $page->id) {
            throw ValidationException::withMessages([
                'section' => ['Section does not belong to the requested tenant page.'],
            ]);
        }

        return [$page, $section];
    }

    private function touchDraft(UiPage $page): void
    {
        $page->draft_revision++;
        $page->save();
    }
}
