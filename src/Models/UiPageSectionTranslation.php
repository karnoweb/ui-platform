<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UiPageSectionTranslation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'content' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(UiPageSection::class, 'section_id');
    }
}
