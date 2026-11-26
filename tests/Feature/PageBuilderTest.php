<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Feature;

use Illuminate\Validation\ValidationException;
use KarnoWeb\TenantUi\Exceptions\CompatibilityException;
use KarnoWeb\TenantUi\Exceptions\DraftConflictException;
use KarnoWeb\TenantUi\Exceptions\GlobalPublishImpactException;
use KarnoWeb\TenantUi\Exceptions\ModuleDisableImpactException;
use KarnoWeb\TenantUi\Exceptions\PublishedVersionNotFoundException;
use KarnoWeb\TenantUi\Models\TenantUiConfig;
use KarnoWeb\TenantUi\Models\UiPageRoute;
use KarnoWeb\TenantUi\Tests\TestCase;
use KarnoWeb\TenantUi\Tests\TestModuleResolver;

final class PageBuilderTest extends TestCase
{
    private string $tenant = 'tenant-1';

    protected function tearDown(): void
    {
        TestModuleResolver::$modules = [
            'search' => true,
            'stories' => true,
        ];

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        TenantUiConfig::query()->create([
            'tenant_key' => $this->tenant,
            'theme_key' => 'modern',
            'preset_key' => 'modern-shop',
            'draft_revision' => 1,
        ]);

        $this->tenantUi($this->tenant)->global()->publish();
    }

    public function test_create_page_and_duplicate_page_key_rejected(): void
    {
        $pages = $this->tenantUi($this->tenant)->pages();

        $pages->createPage('home', 'home');

        $this->expectException(\Illuminate\Database\QueryException::class);
        $pages->createPage('home', 'home');
    }

    public function test_add_section_reorder_and_schema_validation(): void
    {
        $pages = $this->tenantUi($this->tenant)->pages();

        $page = $pages->createPage('home', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'خانه', 'slug' => '/'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $hero = $pages->addSection(
            $page->id,
            'hero',
            'hero-2',
            [],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $products = $pages->addSection(
            $page->id,
            'products',
            'products-3',
            ['limit' => 8],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $story = $pages->addSection(
            $page->id,
            'story',
            'story-2',
            [],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->reorder(
            $page->id,
            [$story->instance_key, $hero->instance_key, $products->instance_key],
            $page->draft_revision,
        );

        $page = $page->fresh(['sections']);

        $this->assertSame(
            [$story->instance_key, $hero->instance_key, $products->instance_key],
            $page->sections->pluck('instance_key')->all()
        );
    }

    public function test_reject_section_not_allowed_by_page_type(): void
    {
        $pages = $this->tenantUi($this->tenant)->pages();
        $page = $pages->createPage('home', 'home');

        $this->expectException(ValidationException::class);

        $pages->addSection(
            $page->id,
            'unknown-type',
            'hero-2',
            [],
            $page->draft_revision,
        );
    }

    public function test_revision_conflict_on_page_mutation(): void
    {
        $pages = $this->tenantUi($this->tenant)->pages();
        $page = $pages->createPage('home', 'home');

        $this->expectException(DraftConflictException::class);

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'x', 'slug' => '/'],
            99,
        );
    }

    public function test_story_section_rejected_when_module_disabled_on_publish(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('home', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'خانه', 'slug' => '/'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->addSection(
            $page->id,
            'story',
            'story-2',
            [],
            $page->draft_revision,
        );

        TestModuleResolver::$modules['stories'] = false;

        $this->expectException(CompatibilityException::class);

        try {
            $scope->pages()->publish($page->id);
        } finally {
            TestModuleResolver::$modules['stories'] = true;
        }
    }

    public function test_disabled_section_excluded_from_published_payload(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('home', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'خانه', 'slug' => '/'],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $section = $pages->addSection(
            $page->id,
            'hero',
            'hero-2',
            [],
            $page->draft_revision,
        );

        $page = $page->fresh();

        $pages->updateSection(
            $section->id,
            'hero-2',
            [],
            false,
            $page->draft_revision,
        );

        $version = $scope->pages()->publish($page->id);

        $this->assertSame([], $version->payload['sections']);
    }

    public function test_deactivate_page_on_publish_removes_runtime_access(): void
    {
        $scope = $this->tenantUi($this->tenant);
        $pages = $scope->pages();
        $page = $pages->createPage('live', 'home');

        $pages->upsertPageTranslation(
            $page->id,
            'fa',
            ['title' => 'Live', 'slug' => 'live'],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $page = $page->fresh();
        $pages->setActive($page->id, false, $page->draft_revision);
        $page = $page->fresh();

        $scope->pages()->publish($page->id);

        $this->assertFalse(
            UiPageRoute::query()->where('page_id', $page->id)->exists()
        );

        $this->expectException(PublishedVersionNotFoundException::class);
        $scope->runtime()->byPageKey('live', 'fa');
    }

    public function test_cross_tenant_section_mutation_is_blocked(): void
    {
        TenantUiConfig::query()->create([
            'tenant_key' => 'tenant-2',
            'theme_key' => 'modern',
            'draft_revision' => 1,
        ]);

        $page = $this->tenantUi('tenant-1')->pages()->createPage('home', 'home');
        $other = $this->tenantUi('tenant-2')->pages()->createPage('other', 'home');

        $section = $this->tenantUi('tenant-1')->pages()->addSection(
            $page->id,
            'hero',
            'hero-2',
            [],
            $page->draft_revision,
        );

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->tenantUi('tenant-2')->pages()->updateSection(
            $section->id,
            'hero-2',
            [],
            true,
            $other->draft_revision,
        );
    }

    public function test_module_disable_impact_analyzer(): void
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
            'story',
            'story-2',
            [],
            $page->draft_revision,
        );

        $scope->pages()->publish($page->id);

        $this->expectException(ModuleDisableImpactException::class);
        $scope->impact()->assertCanDisableModule('stories');
    }

    public function test_module_impact_includes_published_global_header(): void
    {
        TenantUiConfig::query()
            ->where('tenant_key', $this->tenant)
            ->update([
                'overrides' => ['header' => 'header-5'],
            ]);

        $this->tenantUi($this->tenant)->global()->publish();

        $usages = $this->tenantUi($this->tenant)->impact()->moduleUsages('search');

        $this->assertNotEmpty($usages['global']);
        $this->assertSame('header', $usages['global'][0]['component']);

        $this->expectException(ModuleDisableImpactException::class);
        $this->tenantUi($this->tenant)->impact()->assertCanDisableModule('search');
    }
}
