<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Contracts;

interface TenantResolverContract
{
    public function currentKey(): string;
}
