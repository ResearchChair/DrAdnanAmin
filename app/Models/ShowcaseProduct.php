<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShowcaseProduct extends Model
{
    protected $fillable = [
        'name',
        'tagline',
        'description',
        'url',
        'logo_path',
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
}
