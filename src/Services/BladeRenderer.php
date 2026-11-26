<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use KarnoWeb\TenantUi\Contracts\MemoizableDataProviderContract;
use KarnoWeb\TenantUi\Contracts\UiDataProviderContract;
use KarnoWeb\TenantUi\Definitions\VariantDefinition;
use KarnoWeb\TenantUi\DTO\RenderContext;
use KarnoWeb\TenantUi\Exceptions\TenantUiException;
use KarnoWeb\TenantUi\Registries\DataProviderRegistry;
use KarnoWeb\TenantUi\Registries\VariantRegistry;

final readonly class BladeRenderer
{
    public function __construct(
        private VariantRegistry $variants,
        private DataProviderRegistry $providers,
        private Container $container,
        private RenderDataMemoizer $memoizer,
    ) {
    }

    public function renderGlobal(
        array $runtime,
        string $type,
        array $extra = [],
    ): HtmlString {
        $variantKey = $runtime['components'][$type] ?? null;

        if (! $variantKey) {
            return new HtmlString('');
        }

        $variant = $this->variants->get($variantKey);

        if ($variant->scope !== VariantDefinition::SCOPE_GLOBAL) {
            throw new TenantUiException(
                "Variant [{$variantKey}] is not a global variant."
            );
        }

        return new HtmlString(
            $this->renderVariant(
                runtime: $runtime,
                variant: $variant,
                config: [],
                content: [],
                section: null,
                extra: $extra,
            )
        );
    }

    public function renderSections(
        array $runtime,
        array $extra = [],
    ): HtmlString {
        $html = '';

        foreach ($runtime['sections'] ?? [] as $section) {
            $variant = $this->variants->get($section['variant']);

            $html .= $this->renderVariant(
                runtime: $runtime,
                variant: $variant,
                config: $section['config'] ?? [],
                content: $section['content'] ?? [],
                section: $section,
                extra: $extra,
            );
        }

        return new HtmlString($html);
    }

    private function renderVariant(
        array $runtime,
        VariantDefinition $variant,
        array $config,
        array $content,
        ?array $section,
        array $extra,
    ): string {
        if (! $variant->bladeView) {
            throw new TenantUiException(
                "Variant [{$variant->key}] has no Blade view."
            );
        }

        if (! View::exists($variant->bladeView)) {
            throw new TenantUiException(
                "Blade view [{$variant->bladeView}] does not exist."
            );
        }

        $providerClass = $this->providers->get(
            $variant->scope,
            $variant->type,
        );

        $data = [];

        if ($providerClass) {
            $context = new RenderContext(
                tenantKey: $runtime['tenant'],
                locale: $runtime['locale'],
                pageKey: $runtime['page']['key'] ?? null,
                pageType: $runtime['page']['type'] ?? null,
                sectionInstanceKey: $section['instance_key'] ?? null,
                extra: $extra,
            );

            $provider = $this->container->make($providerClass);

            if (! $provider instanceof UiDataProviderContract) {
                throw new TenantUiException(
                    "Provider [{$providerClass}] must implement UiDataProviderContract."
                );
            }

            $memoKey = $provider instanceof MemoizableDataProviderContract
                ? $providerClass.':'.$provider->memoKey($context, $config)
                : implode(':', [
                    $providerClass,
                    $runtime['tenant'],
                    $runtime['locale'],
                    $runtime['page']['key'] ?? '',
                    $section['instance_key'] ?? '',
                    $variant->scope,
                    $variant->type,
                    hash('xxh128', json_encode($config, JSON_THROW_ON_ERROR)),
                    hash('xxh128', json_encode($extra, JSON_THROW_ON_ERROR)),
                ]);

            $data = $this->memoizer->remember(
                $memoKey,
                static fn (): array => $provider->provide($context, $config),
            );
        }

        return View::make($variant->bladeView, [
            'config' => $config,
            'content' => $content,
            'data' => $data,
            'section' => $section,
            'runtime' => $runtime,
            'extra' => $extra,
        ])->render();
    }
}
