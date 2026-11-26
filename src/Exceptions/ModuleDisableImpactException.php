<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Exceptions;

final class ModuleDisableImpactException extends TenantUiException
{
    /**
     * @param  array{
     *     global?: list<array{component: string, variant: string}>,
     *     pages?: list<array{page_key: string, section_type: string, variant: string}>
     * }  $usages
     */
    public function __construct(
        string $message,
        public readonly array $usages = [],
    ) {
        parent::__construct($message);
    }
}
