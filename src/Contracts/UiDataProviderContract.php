<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Contracts;

use KarnoWeb\TenantUi\DTO\RenderContext;

/**
 * Supplies Blade/runtime data for a variant.
 *
 * Performance expectations (host app implementations):
 * - Keep DB/query count bounded per render; prefer eager loading.
 * - Never query inside loops over catalog rows.
 * - Apply limits/pagination where listing data.
 * - Optional application-level cache for expensive aggregates.
 *
 * Request-scoped deduplication is handled by {@see \KarnoWeb\TenantUi\Services\RenderDataMemoizer};
 * it does not replace query optimization.
 */
interface UiDataProviderContract
{
    public function provide(RenderContext $context, array $config): array;
}
