<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Illuminate\Support\Facades\DB;
use KarnoWeb\TenantUi\Events\TenantUiPublished;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\TenantUiVersion;
use KarnoWeb\TenantUi\Support\VersionChecksum;

final readonly class TenantUiPublisher
{
    public function __construct(
        private TenantUiResolver $resolver,
        private PublishedReleaseValidator $releaseValidator,
        private RuntimeCache $cache,
    ) {
    }

    public function publish(
        string $tenantKey,
        ?string $publishedBy = null,
    ): TenantUiVersion {
        $version = DB::transaction(function () use ($tenantKey, $publishedBy) {
            /** @var TenantUiConfig $config */
            $config = TenantUiConfig::query()
                ->where('tenant_key', $tenantKey)
                ->lockForUpdate()
                ->firstOrFail();

            $payload = $this->resolver->resolve($config);

            $this->releaseValidator->assertGlobalPublishCompatible(
                $tenantKey,
                $payload,
            );

            $checksum = VersionChecksum::hash($payload);

            if ($config->published_version_id) {
                /** @var TenantUiVersion|null $current */
                $current = TenantUiVersion::query()->find($config->published_version_id);

                if ($current && $current->checksum === $checksum) {
                    return $current;
                }
            }

            $nextVersion = ((int) TenantUiVersion::query()
                ->where('tenant_key', $tenantKey)
                ->max('version')) + 1;

            /** @var TenantUiVersion $version */
            $version = TenantUiVersion::query()->create([
                'tenant_key' => $tenantKey,
                'version' => $nextVersion,
                'payload' => $payload,
                'checksum' => $checksum,
                'published_by' => $publishedBy,
                'published_at' => now(),
            ]);

            $config->forceFill([
                'published_version_id' => $version->id,
            ])->save();

            return $version;
        });

        $pointerKey = $this->cache->key('pointer', 'global', $tenantKey);
        $versionKey = $this->cache->key('global', $tenantKey, (string) $version->id);

        $this->cache->forget($pointerKey);
        $this->cache->put($pointerKey, $version->id, RuntimeCache::TTL_POINTER);
        $this->cache->put($versionKey, $version->payload, RuntimeCache::TTL_VERSION);

        event(new TenantUiPublished(
            tenantKey: $tenantKey,
            versionId: $version->id,
            publishedBy: $publishedBy,
        ));

        return $version;
    }
}
