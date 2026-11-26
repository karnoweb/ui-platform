<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class UiPageSection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
        'is_enabled' => 'boolean',
        'config' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(UiPage::class, 'page_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(UiPageSectionTranslation::class, 'section_id');
    }
}
