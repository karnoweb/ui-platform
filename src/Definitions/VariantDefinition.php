<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Definitions;

use InvalidArgumentException;

final readonly class VariantDefinition
{
    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_SECTION = 'section';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DEPRECATED = 'deprecated';
    public const STATUS_DISABLED = 'disabled';

    public function __construct(
        public string $key,
        public string $type,
        public string $scope,
        public string $label,
        public ?string $bladeView = null,
        public array $requiresModules = [],
        public array $optionalModules = [],
        public array $supportedLayouts = [],
        public array $requiredThemeCapabilities = [],
        public array $allowedPageTypes = [],
        public array $configFields = [],
        public array $translationFields = [],
        public string $status = self::STATUS_ACTIVE,
        public array $meta = [],
    ) {
        if (! in_array($scope, [self::SCOPE_GLOBAL, self::SCOPE_SECTION], true)) {
            throw new InvalidArgumentException("Invalid variant scope [{$scope}].");
        }

        if (! in_array($status, [
            self::STATUS_ACTIVE,
            self::STATUS_DEPRECATED,
            self::STATUS_DISABLED,
        ], true)) {
            throw new InvalidArgumentException("Invalid variant status [{$status}].");
        }
    }
}
