<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Feature;

use KarnoWeb\TenantUi\Contracts\LocaleManagerContract;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Services\RuntimeCache;
use KarnoWeb\TenantUi\Tests\ConfigurableTestLocaleManager;
use KarnoWeb\TenantUi\Tests\TestCase;

final class RuntimeFallbackLocaleCacheTest extends TestCase
{
    private string $tenant = 'tenant-1';

    private ConfigurableTestLocaleManager $locales;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $this->locales = new ConfigurableTestLocaleManager();
        $this->locales->setFallback('en');
        $this->locales->setSupported(['fa', 'en', 'de']);

        $app->singleton(LocaleManagerContract::class, fn (): ConfigurableTestLocaleManager => $this->locales);
    }

    protected function setUp(): void
    {
        parent::setUp();

        TenantUiConfig::query()->create([
            'tenant_key' => $this->tenant,
            'theme_key' => 'modern',
            'preset_key' => 'modern-shop',
            'overrides' => ['header' => 'header-1'],
            'draft_revision' => 1,
        ]);

        $this->tenantUi($this->tenant)->global()->publish();
    }

    public function test_runtime_cache_key_includes_fallback_locale(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('intl', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'FA title', 'slug' => 'intl-fa'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->upsertPageTranslation(
            $page->id,
            'en',
            ['title' => 'EN title', 'slug' => 'intl-en'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $section = $pages->addSection(
            $page->id,
            'products',
            'products-3',
            [],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->upsertSectionTranslation(
            $section->id,
            'en',
            ['title' => 'English section'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->upsertSectionTranslation(
            $section->id,
            'fa',
            ['title' => 'Persian section'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $runtime = $scope->runtime();

        $this->locales->setFallback('en');
        $first = $runtime->byPageKey('intl', 'de');
        $this->assertSame('English section', $first['sections'][0]['content']['title']);
        $this->assertSame('en', $first['fallback_locale']);

        $this->locales->setFallback('fa');

        $second = $runtime->byPageKey('intl', 'de');
        $this->assertSame('Persian section', $second['sections'][0]['content']['title']);
        $this->assertSame('fa', $second['fallback_locale']);
    }

    public function test_changing_fallback_invalidates_distinct_runtime_cache_entry(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('intl2', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'en',
            ['title' => 'EN', 'slug' => 'intl2'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $section = $pages->addSection(
            $page->id,
            'products',
            'products-3',
            [],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->upsertSectionTranslation(
            $section->id,
            'en',
            ['title' => 'From EN fallback'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $cache = app(RuntimeCache::class);
        $this->locales->setFallback('en');

        $scope->runtime()->byPageKey('intl2', 'de');

        $this->locales->setFallback('fa');

        $config = TenantUiConfig::query()
            ->where('tenant_key', $this->tenant)
            ->firstOrFail();

        $globalVersionId = (int) $config->published_version_id;
        $pageModel = $page->fresh();
        $pageVersionId = (int) $pageModel->published_version_id;

        $enFallbackKey = $cache->key(
            'runtime',
            $this->tenant,
            (string) $globalVersionId,
            (string) $pageVersionId,
            'de',
            'en',
        );

        $faFallbackKey = $cache->key(
            'runtime',
            $this->tenant,
            (string) $globalVersionId,
            (string) $pageVersionId,
            'de',
            'fa',
        );

        $this->assertNotSame($enFallbackKey, $faFallbackKey);
    }
}
