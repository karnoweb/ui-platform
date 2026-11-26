<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Exceptions;

final class DraftConflictException extends TenantUiException
{
    public static function revision(
        int $expected,
        int $actual,
    ): self {
        return new self(
            "Draft revision conflict. Expected [{$expected}], actual [{$actual}]."
        );
    }
}
