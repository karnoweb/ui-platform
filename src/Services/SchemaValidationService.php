<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Services;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Arr;

final readonly class SchemaValidationService
{
    public function __construct(
        private ValidationFactory $validator,
    ) {
    }

    public function withDefaults(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            $key = $field['key'];

            if (
                ! Arr::has($data, $key)
                && array_key_exists('default', $field)
            ) {
                Arr::set($data, $key, $field['default']);
            }
        }

        return $data;
    }

    public function validate(array $data, array $fields): array
    {
        $data = $this->withDefaults($data, $fields);

        $rules = [];

        foreach ($fields as $field) {
            $rules[$field['key']] = $field['rules'] ?? [];
        }

        return $this->validator->make($data, $rules)->validate();
    }
}
