<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Exceptions\CompatibilityException;
use KarnoWeb\TenantUi\Exceptions\GlobalPublishImpactException;
use KarnoWeb\TenantUi\Models\UiPage;
use KarnoWeb\TenantUi\Models\UiPageVersion;

final readonly class PublishedReleaseValidator
{
    public function __construct(
        private CompatibilityService $compatibility,
    ) {
    }

    public function assertGlobalPublishCompatible(
        string $tenantKey,
        array $resolvedGlobalPayload,
    ): void {
        $themeKey = (string) $resolvedGlobalPayload['theme'];
        $issuesByPage = [];

        $pages = UiPage::query()
            ->where('tenant_key', $tenantKey)
            ->whereNotNull('published_version_id')
            ->get();

        foreach ($pages as $page) {
            /** @var UiPageVersion|null $version */
            $version = UiPageVersion::query()->find($page->published_version_id);

            if (! $version) {
                continue;
            }

            $pageIssues = [];

            foreach ($version->payload['sections'] ?? [] as $section) {
                try {
                    $this->compatibility->assertVariant(
                        tenantKey: $tenantKey,
                        themeKey: $themeKey,
                        variantKey: (string) $section['variant'],
                        expectedScope: VariantDefinition::SCOPE_SECTION,
                        expectedType: (string) $section['type'],
                        pageType: $page->type,
                    );
                } catch (CompatibilityException $exception) {
                    $pageIssues[] = sprintf(
                        '%s/%s: %s',
                        $section['type'] ?? '?',
                        $section['variant'] ?? '?',
                        $exception->getMessage(),
                    );
                }
            }

            if ($pageIssues !== []) {
                $issuesByPage[] = [
                    'page_key' => $page->page_key,
                    'issues' => $pageIssues,
                ];
            }
        }

        if ($issuesByPage !== []) {
            throw new GlobalPublishImpactException(
                'Cannot publish global UI; published pages are incompatible with the proposed theme.',
                $issuesByPage,
            );
        }
    }
}
