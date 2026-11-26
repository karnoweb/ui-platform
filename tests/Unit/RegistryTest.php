<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Tests\Unit;

use InvalidArgumentException;
use KarnoWeb\TenantUi\Definitions\PageTypeDefinition;
use KarnoWeb\TenantUi\Definitions\PresetDefinition;
use KarnoWeb\TenantUi\Definitions\ThemeDefinition;
use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\Registries\PageTypeRegistry;
use KarnoWeb\TenantUi\Registries\PresetRegistry;
use KarnoWeb\TenantUi\Registries\ThemeRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;
use KarnoWeb\TenantUi\Tests\TestCase;

final class RegistryTest extends TestCase
{
    public function test_duplicate_theme_throws(): void
    {
        $registry = new ThemeRegistry;

        $registry->register(new ThemeDefinition('x', 'X', 'full-width'));

        $this->expectException(InvalidArgumentException::class);
        $registry->register(new ThemeDefinition('x', 'X2', 'boxed'));
    }

    public function test_duplicate_variant_throws(): void
    {
        $registry = new VariantRegistry;

        $registry->register(new VariantDefinition(
            'v1',
            'header',
            VariantDefinition::SCOPE_GLOBAL,
            'V1',
        ));

        $this->expectException(InvalidArgumentException::class);
        $registry->register(new VariantDefinition(
            'v1',
            'header',
            VariantDefinition::SCOPE_GLOBAL,
            'V1 again',
        ));
    }

    public function test_unknown_variant_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(VariantRegistry::class)->get('missing-variant');
    }

    public function test_preset_references_valid_in_doctor(): void
    {
        $this->artisan('tenant-ui:doctor')
            ->assertSuccessful();
    }
}
