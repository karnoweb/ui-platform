<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Exceptions;

final class GlobalPublishImpactException extends TenantUiException
{
    /**
     * @param  list<array{page_key: string, issues: list<string>}>  $pages
     */
    public function __construct(
        string $message,
        public readonly array $pages = [],
    ) {
        parent::__construct($message);
    }
}
