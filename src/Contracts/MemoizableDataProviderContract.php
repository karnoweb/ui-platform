<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Contracts;

use KarnoWeb\TenantUi\DTO\RenderContext;

interface MemoizableDataProviderContract extends UiDataProviderContract
{
    /**
     * Return the provider-specific portion of the memo key.
     * The package prefixes this with the provider class FQCN to avoid cross-provider collisions.
     */
    public function memoKey(RenderContext $context, array $config): string;
}
