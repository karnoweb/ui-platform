<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Models;

use Illuminate\Database\Eloquent\Model;

final class TenantUiVersion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'version' => 'integer',
        'payload' => 'array',
        'published_at' => 'immutable_datetime',
    ];
}
