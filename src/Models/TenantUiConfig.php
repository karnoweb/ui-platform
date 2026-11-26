<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantUiConfig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'overrides' => 'array',
        'draft_revision' => 'integer',
        'published_version_id' => 'integer',
    ];
}
