<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UiPageTranslation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'extra' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(UiPage::class, 'page_id');
    }
}
