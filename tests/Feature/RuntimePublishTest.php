<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use KarnoWeb\TenantUi\Events\PagePublished;
use KarnoWeb\TenantUi\Exceptions\CompatibilityException;
use KarnoWeb\TenantUi\Exceptions\GlobalPublishImpactException;
use KarnoWeb\TenantUi\Exceptions\PublishedVersionNotFoundException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\UiPageRoute;
use KarnoWeb\TenantUi\Services\RuntimeCache;
use KarnoWeb\TenantUi\Tests\TestCase;

final class RuntimePublishTest extends TestCase
{
    private string $tenant = 'tenant-1';

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

    public function test_draft_inactive_does_not_unpublish_until_publish(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('live-draft', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'Live', 'slug' => 'live-draft'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $page = $page->fresh();
        $pages->setActive($page->id, false, $page->draft_revision);

        app(RuntimeCache::class)->forget(
            app(RuntimeCache::class)->key('pointer', 'page', $this->tenant, 'live-draft'),
        );

        $payload = $scope->runtime()->byPageKey('live-draft', 'fa');
        $this->assertSame('live-draft', $payload['page']['key']);
    }

    public function test_draft_changes_do_not_affect_runtime_until_publish(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('home', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'صفحه اصلی', 'slug' => '/'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $section = $pages->addSection(
            $page->id,
            'products',
            'products-3',
            ['limit' => 12],
            $page->draft_revision,
        );

        $page = $page->fresh(['sections']);

        $pages->upsertSectionTranslation(
            $section->id,
            'fa',
            ['title' => 'پرفروش‌ترین‌ها'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $runtime = $scope->runtime();

        $before = $runtime->byPageKey('home', 'fa');

        $scope->global()->draft()->save(
            themeKey: 'modern',
            presetKey: 'modern-shop',
            overrides: ['header' => 'header-5'],
            expectedRevision: 1,
        );

        $afterDraft = $runtime->byPageKey('home', 'fa');

        $this->assertSame(
            $before['components']['header'],
            $afterDraft['components']['header'],
        );

        $scope->global()->publish();

        $afterPublish = $runtime->byPageKey('home', 'fa');

        $this->assertSame('header-5', $afterPublish['components']['header']);
    }

    public function test_page_publish_validates_against_published_global_theme(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('boxed-page', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'Boxed', 'slug' => 'boxed'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $scope->global()->draft()->save(
            themeKey: 'boxed',
            presetKey: null,
            overrides: ['header' => 'header-default'],
            expectedRevision: 1,
        );

        $pages->addSection(
            $page->id,
            'banner',
            'banner-boxed',
            [],
            $page->draft_revision,
        );

        $this->expectException(CompatibilityException::class);
        $scope->pages()->publish($page->id);
    }

    public function test_global_publish_rejects_incompatible_published_pages(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('home', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'Home', 'slug' => '/'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->addSection(
            $page->id,
            'hero',
            'hero-2',
            [],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $scope->global()->draft()->save(
            themeKey: 'boxed',
            presetKey: null,
            overrides: ['header' => 'header-default'],
            expectedRevision: 1,
        );

        $this->expectException(GlobalPublishImpactException::class);
        $scope->global()->publish();
    }

    public function test_locale_fallback_for_section_content(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('about', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'درباره', 'slug' => 'about-fa'],
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
            'fa',
            ['title' => 'فارسی'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $payload = $scope->runtime()->byPageKey('about', 'en');

        $this->assertSame('فارسی', $payload['sections'][0]['content']['title']);
    }

    public function test_by_slug_resolves_published_route_without_extra_page_query_when_warm(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('contact', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'تماس', 'slug' => 'contact-us'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $scope->runtime()->bySlug('contact-us', 'fa');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $payload = $scope->runtime()->bySlug('contact-us', 'fa');

        $this->assertSame('contact', $payload['page']['key']);
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_old_slug_cache_is_invalidated_after_publish(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('landing', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'L', 'slug' => 'old-slug'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $scope->runtime()->bySlug('old-slug', 'fa');

        $page = $page->fresh();
        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'L2', 'slug' => 'new-slug'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $this->expectException(PublishedVersionNotFoundException::class);
        $scope->runtime()->bySlug('old-slug', 'fa');
    }

    public function test_page_route_updates_only_after_publish(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('landing2', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'L', 'slug' => 'old-slug-2'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $this->assertTrue(
            UiPageRoute::query()
                ->where('slug', 'old-slug-2')
                ->exists()
        );

        $page = $page->fresh();
        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'L2', 'slug' => 'new-slug-2'],
            $page->draft_revision,
        );

        $this->assertFalse(
            UiPageRoute::query()
                ->where('slug', 'new-slug-2')
                ->exists()
        );

        $scope->pages()->publish($page->id);

        $this->assertFalse(
            UiPageRoute::query()
                ->where('slug', 'old-slug-2')
                ->exists()
        );

        $this->assertTrue(
            UiPageRoute::query()
                ->where('slug', 'new-slug-2')
                ->exists()
        );
    }

    public function test_unpublished_page_throws(): void
    {
        $this->tenantUi($this->tenant)->pages()->createPage('draft-only', 'home');

        $this->expectException(PublishedVersionNotFoundException::class);

        $this->tenantUi($this->tenant)->runtime()->byPageKey('draft-only', 'fa');
    }

    public function test_warm_runtime_payload_minimizes_db_queries(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('cached', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'C', 'slug' => 'cached'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $runtime = $scope->runtime();

        $runtime->byPageKey('cached', 'fa');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $second = $runtime->byPageKey('cached', 'fa');

        $this->assertNotEmpty($second);
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_publish_dispatches_page_event(): void
    {
        Event::fake([PagePublished::class]);

        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('evt', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'E', 'slug' => 'evt'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        Event::assertDispatched(PagePublished::class);
    }
}
