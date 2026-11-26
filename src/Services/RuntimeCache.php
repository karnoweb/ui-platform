<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RuntimeCache
{
    private const CACHE_MISS = '__tenant_ui_cache_miss__';

    public const TTL_POINTER = 'pointer';

    public const TTL_VERSION = 'version';

    public const TTL_RUNTIME = 'runtime';

    public const TTL_ROUTE = 'route';

    private Repository $cache;

    public function __construct(?Repository $repository = null)
    {
        if ($repository !== null) {
            $this->cache = $repository;

            return;
        }

        $store = config('tenant-ui.cache.store') ?? config('cache.default');

        $this->cache = Cache::store($store);
    }

    public function key(string ...$parts): string
    {
        $prefix = (string) config('tenant-ui.cache.prefix', 'tenant-ui');

        return $prefix.':'.implode(':', $parts);
    }

    public function remember(
        string $key,
        Closure $callback,
        string $ttlProfile = self::TTL_RUNTIME,
    ): mixed {
        $cached = $this->get($key);

        if ($cached !== self::CACHE_MISS) {
            return $cached;
        }

        if ($this->usesStampedeLock($ttlProfile)) {
            return $this->rememberWithStampedeLock($key, $callback, $ttlProfile);
        }

        $value = $callback();

        $this->put($key, $value, $ttlProfile);

        return $value;
    }

    public function put(
        string $key,
        mixed $value,
        string $ttlProfile = self::TTL_RUNTIME,
    ): void {
        try {
            $this->cache->put($key, $value, $this->ttl($ttlProfile));
        } catch (Throwable $exception) {
            Log::warning('Tenant UI cache put failed.', [
                'key' => $key,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function forget(string $key): void
    {
        try {
            $this->cache->forget($key);
        } catch (Throwable $exception) {
            Log::warning('Tenant UI cache forget failed.', [
                'key' => $key,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function ttl(string $profile): int
    {
        $configured = (array) config('tenant-ui.cache.ttl', []);

        if (isset($configured[$profile])) {
            return (int) $configured[$profile];
        }

        return (int) config('tenant-ui.cache.ttl_seconds', 86400);
    }

    private function get(string $key): mixed
    {
        try {
            $value = $this->cache->get($key, self::CACHE_MISS);

            return $value === self::CACHE_MISS ? self::CACHE_MISS : $value;
        } catch (Throwable $exception) {
            Log::warning('Tenant UI cache get failed; treating as miss.', [
                'key' => $key,
                'message' => $exception->getMessage(),
            ]);

            return self::CACHE_MISS;
        }
    }

    private function rememberWithStampedeLock(
        string $key,
        Closure $callback,
        string $ttlProfile,
    ): mixed {
        $lockSeconds = (int) config('tenant-ui.cache.stampede.lock_seconds', 10);
        $blockSeconds = (int) config('tenant-ui.cache.stampede.block_seconds', 5);
        $lock = $this->cache->lock($this->lockKey($key), $lockSeconds);

        try {
            return $lock->block($blockSeconds, function () use (
                $key,
                $callback,
                $ttlProfile,
            ): mixed {
                $cached = $this->get($key);

                if ($cached !== self::CACHE_MISS) {
                    return $cached;
                }

                $value = $callback();

                $this->put($key, $value, $ttlProfile);

                return $value;
            });
        } catch (LockTimeoutException $exception) {
            Log::warning('Tenant UI cache stampede lock timed out; loading without cache write.', [
                'key' => $key,
                'message' => $exception->getMessage(),
            ]);

            $cached = $this->get($key);

            if ($cached !== self::CACHE_MISS) {
                return $cached;
            }

            return $callback();
        } catch (Throwable $exception) {
            Log::warning('Tenant UI cache stampede lock failed; loading without cache write.', [
                'key' => $key,
                'message' => $exception->getMessage(),
            ]);

            return $callback();
        }
    }

    private function usesStampedeLock(string $ttlProfile): bool
    {
        if (! (bool) config('tenant-ui.cache.stampede.enabled', true)) {
            return false;
        }

        $profiles = (array) config('tenant-ui.cache.stampede.profiles', [
            self::TTL_POINTER,
            self::TTL_ROUTE,
            self::TTL_RUNTIME,
        ]);

        return in_array($ttlProfile, $profiles, true);
    }

    private function lockKey(string $key): string
    {
        return $key.':stampede-lock';
    }
}
