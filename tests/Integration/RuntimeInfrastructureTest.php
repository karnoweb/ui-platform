<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Integration;

use KarnoWeb\TenantUi\Tests\TestCase;

/**
 * Infrastructure checks against real Redis/MySQL in the host CI environment.
 *
 * @group integration
 */
final class RuntimeInfrastructureTest extends TestCase
{
    protected function setUp(): void
    {
        if (! getenv('TENANT_UI_INTEGRATION')) {
            $this->markTestSkipped(
                'Set TENANT_UI_INTEGRATION=1 and configure Redis/MySQL to run infrastructure tests.'
            );
        }

        parent::setUp();
    }

    public function test_redis_cache_store_can_remember_with_stampede_lock(): void
    {
        $store = getenv('TENANT_UI_CACHE_STORE') ?: 'redis';

        config([
            'cache.default' => $store,
            'tenant-ui.cache.store' => $store,
            'tenant-ui.cache.stampede.enabled' => true,
        ]);

        $cache = app(\KarnoWeb\TenantUi\Services\RuntimeCache::class);
        $calls = 0;

        $value = $cache->remember(
            $cache->key('integration', 'stampede'),
            function () use (&$calls): string {
                $calls++;

                return 'warm';
            },
            \KarnoWeb\TenantUi\Services\RuntimeCache::TTL_POINTER,
        );

        $this->assertSame('warm', $value);
        $this->assertSame(1, $calls);

        $again = $cache->remember(
            $cache->key('integration', 'stampede'),
            function () use (&$calls): string {
                $calls++;

                return 'warm';
            },
            \KarnoWeb\TenantUi\Services\RuntimeCache::TTL_POINTER,
        );

        $this->assertSame('warm', $again);
        $this->assertSame(1, $calls);
    }
}
