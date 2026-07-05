<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResearchActivity extends Model
{
    protected $fillable = [
        'type',
        'title',
        'organization',
        'role',
        'year',
        'year_end',
        'description',
        'url',
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

    public function getTypeLabelAttribute(): string
    {
        return config('academic.activity_types.'.$this->type, ucfirst($this->type));
    }
}
