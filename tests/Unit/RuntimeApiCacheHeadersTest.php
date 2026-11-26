<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Unit;

use KarnoWeb\TenantUi\Support\RuntimeApiCacheHeaders;
use KarnoWeb\TenantUi\Tests\TestCase;

final class RuntimeApiCacheHeadersTest extends TestCase
{
    public function test_cache_control_defaults_to_private(): void
    {
        config([
            'tenant-ui.runtime_api.cache_max_age' => 120,
            'tenant-ui.runtime_api.cache_visibility' => 'private',
        ]);

        $this->assertSame('private, max-age=120', RuntimeApiCacheHeaders::cacheControl());
    }

    public function test_cache_control_can_opt_in_to_public(): void
    {
        config([
            'tenant-ui.runtime_api.cache_max_age' => 60,
            'tenant-ui.runtime_api.cache_visibility' => 'public',
        ]);

        $this->assertSame('public, max-age=60', RuntimeApiCacheHeaders::cacheControl());
    }
}
