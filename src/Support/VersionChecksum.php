<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Support;

final class VersionChecksum
{
    public static function hash(array $payload): string
    {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        return hash('sha256', $json);
    }
}
