<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Illuminate\Support\Facades\DB;
use KarnoWeb\TenantUi\Exceptions\DraftConflictException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;

final readonly class TenantUiDraftService
{
    public function __construct(
        private TenantUiResolver $resolver,
    ) {
    }

    public function save(
        string $tenantKey,
        string $themeKey,
        ?string $presetKey,
        array $overrides,
        ?int $expectedRevision = null,
    ): TenantUiConfig {
        return DB::transaction(function () use (
            $tenantKey,
            $themeKey,
            $presetKey,
            $overrides,
            $expectedRevision,
        ): TenantUiConfig {
            /** @var TenantUiConfig|null $config */
            $config = TenantUiConfig::query()
                ->where('tenant_key', $tenantKey)
                ->lockForUpdate()
                ->first();

            if (! $config) {
                $config = new TenantUiConfig([
                    'tenant_key' => $tenantKey,
                    'draft_revision' => 1,
                ]);
            } elseif (
                $expectedRevision !== null
                && $config->draft_revision !== $expectedRevision
            ) {
                throw DraftConflictException::revision(
                    $expectedRevision,
                    $config->draft_revision,
                );
            }

            $config->theme_key = $themeKey;
            $config->preset_key = $presetKey;
            $config->overrides = $overrides;

            $this->resolver->resolve($config);

            if ($config->exists) {
                $config->draft_revision++;
            }

            $config->save();

            return $config->fresh();
        });
    }
}
