<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Unit;

use Illuminate\Contracts\Cache\Repository;
use KarnoWeb\TenantUi\Services\RuntimeCache;
use KarnoWeb\TenantUi\Tests\TestCase;
use Mockery;
use RuntimeException;

final class RuntimeCacheTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('tenant-ui.cache.stampede.enabled', false);
    }

    public function test_loader_runs_once_when_loader_throws(): void
    {
        $cache = app(RuntimeCache::class);
        $calls = 0;

        try {
            $cache->remember('tenant-ui:test:throw', function () use (&$calls): never {
                $calls++;

                throw new RuntimeException('database error');
            });
        } catch (RuntimeException) {
        }

        $this->assertSame(1, $calls);
    }

    public function test_loader_runs_once_when_put_fails(): void
    {
        /** @var Repository&\Mockery\MockInterface $repository */
        $repository = Mockery::mock(Repository::class);

        $repository->shouldReceive('get')
            ->once()
            ->andReturnUsing(static fn (string $key, mixed $default): mixed => $default);

        $repository->shouldReceive('put')
            ->once()
            ->andThrow(new RuntimeException('redis write failed'));

        $cache = new RuntimeCache($repository);
        $calls = 0;

        $value = $cache->remember(
            'tenant-ui:test:put-fail',
            function () use (&$calls): string {
                $calls++;

                return 'loaded';
            },
        );

        $this->assertSame('loaded', $value);
        $this->assertSame(1, $calls);
    }

    public function test_get_failure_treats_as_miss_and_loads_once(): void
    {
        /** @var Repository&\Mockery\MockInterface $repository */
        $repository = Mockery::mock(Repository::class);

        $repository->shouldReceive('get')
            ->once()
            ->andThrow(new RuntimeException('redis read failed'));

        $repository->shouldReceive('put')
            ->once()
            ->andReturn(true);

        $cache = new RuntimeCache($repository);
        $calls = 0;

        $value = $cache->remember(
            'tenant-ui:test:get-fail',
            function () use (&$calls): string {
                $calls++;

                return 'ok';
            },
        );

        $this->assertSame('ok', $value);
        $this->assertSame(1, $calls);
    }

    public function test_mutable_pointer_ttl_defaults_to_short_window(): void
    {
        $cache = app(RuntimeCache::class);

        $this->assertSame(300, $cache->ttl(RuntimeCache::TTL_POINTER));
        $this->assertSame(300, $cache->ttl(RuntimeCache::TTL_ROUTE));
        $this->assertSame(604800, $cache->ttl(RuntimeCache::TTL_VERSION));
    }
}
