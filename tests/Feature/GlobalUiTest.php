<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Feature;

use Illuminate\Support\Facades\Event;
use KarnoWeb\TenantUi\Events\TenantUiPublished;
use KarnoWeb\TenantUi\Exceptions\CompatibilityException;
use KarnoWeb\TenantUi\Exceptions\DraftConflictException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Services\TenantUiDraftService;
use KarnoWeb\TenantUi\Services\TenantUiPublisher;
use KarnoWeb\TenantUi\Services\TenantUiResolver;
use KarnoWeb\TenantUi\Tests\TestCase;
use KarnoWeb\TenantUi\Tests\TestModuleResolver;

final class GlobalUiTest extends TestCase
{
    protected function tearDown(): void
    {
        TestModuleResolver::$modules = [
            'search' => true,
            'stories' => true,
        ];

        parent::tearDown();
    }

    public function test_override_beats_preset_beats_default(): void
    {
        $config = new TenantUiConfig([
            'tenant_key' => 't1',
            'theme_key' => 'modern',
            'preset_key' => 'modern-shop',
            'overrides' => ['header' => 'header-1'],
            'draft_revision' => 1,
        ]);

        $resolved = app(TenantUiResolver::class)->resolve($config);

        $this->assertSame('header-1', $resolved['components']['header']);
        $this->assertSame('footer-2', $resolved['components']['footer']);
    }

    public function test_missing_required_module_rejects_resolve(): void
    {
        $config = new TenantUiConfig([
            'tenant_key' => 't1',
            'theme_key' => 'modern',
            'preset_key' => 'modern-shop',
            'draft_revision' => 1,
        ]);

        TestModuleResolver::$modules['search'] = false;

        try {
            $this->expectException(CompatibilityException::class);
            app(TenantUiResolver::class)->resolve($config);
        } finally {
            TestModuleResolver::$modules['search'] = true;
        }
    }

    public function test_deprecated_variant_can_still_publish(): void
    {
        TenantUiConfig::query()->create([
            'tenant_key' => 't-dep',
            'theme_key' => 'modern',
            'overrides' => ['header' => 'header-deprecated'],
            'draft_revision' => 1,
        ]);

        $version = app(TenantUiPublisher::class)->publish('t-dep');

        $this->assertSame(
            'header-deprecated',
            $version->payload['components']['header']
        );
    }

    public function test_draft_change_does_not_modify_published_version(): void
    {
        TenantUiConfig::query()->create([
            'tenant_key' => 't1',
            'theme_key' => 'modern',
            'preset_key' => 'modern-shop',
            'overrides' => ['header' => 'header-1'],
            'draft_revision' => 1,
        ]);

        $published = app(TenantUiPublisher::class)->publish('t1');
        $checksumBefore = $published->checksum;

        app(TenantUiDraftService::class)->save(
            tenantKey: 't1',
            themeKey: 'modern',
            presetKey: 'modern-shop',
            overrides: ['header' => 'header-5'],
            expectedRevision: 1,
        );

        $published->refresh();
        $this->assertSame($checksumBefore, $published->checksum);
    }

    public function test_publish_creates_incrementing_versions_and_event(): void
    {
        Event::fake([TenantUiPublished::class]);

        TenantUiConfig::query()->create([
            'tenant_key' => 't1',
            'theme_key' => 'modern',
            'draft_revision' => 1,
        ]);

        $v1 = app(TenantUiPublisher::class)->publish('t1', 'admin');

        app(TenantUiDraftService::class)->save(
            tenantKey: 't1',
            themeKey: 'modern',
            presetKey: null,
            overrides: ['header' => 'header-1'],
            expectedRevision: 1,
        );

        $v2 = app(TenantUiPublisher::class)->publish('t1', 'admin');

        $this->assertSame(1, $v1->version);
        $this->assertSame(2, $v2->version);
        $this->assertNotSame($v1->id, $v2->id);

        Event::assertDispatched(TenantUiPublished::class);
    }

    public function test_draft_revision_conflict(): void
    {
        TenantUiConfig::query()->create([
            'tenant_key' => 't1',
            'theme_key' => 'modern',
            'draft_revision' => 2,
        ]);

        $this->expectException(DraftConflictException::class);

        app(TenantUiDraftService::class)->save(
            tenantKey: 't1',
            themeKey: 'modern',
            presetKey: null,
            overrides: [],
            expectedRevision: 1,
        );
    }
}
