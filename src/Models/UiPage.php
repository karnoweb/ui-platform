<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class UiPage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'draft_revision' => 'integer',
        'published_version_id' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(UiPageTranslation::class, 'page_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(UiPageSection::class, 'page_id')
            ->orderBy('position');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(UiPageVersion::class, 'page_id');
    }
}
