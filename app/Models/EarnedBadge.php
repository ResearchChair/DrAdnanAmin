<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EarnedBadge extends Model
{
    protected $fillable = [
        'title',
        'issuer',
        'logo_path',
        'url',
        'year',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function logoUrl(): ?string
    {
        return PublicStorage::url($this->logo_path);
    }
}
