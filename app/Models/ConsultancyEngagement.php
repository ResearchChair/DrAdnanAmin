<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConsultancyEngagement extends Model
{
    protected $fillable = [
        'title',
        'organization',
        'role',
        'type',
        'year_start',
        'year_end',
        'location',
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('year_start')
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function getTypeLabelAttribute(): string
    {
        return config('academic.consultancy_types.'.$this->type, ucfirst(str_replace('_', ' ', (string) $this->type)));
    }

    public function yearRangeLabel(): string
    {
        if ($this->year_start && $this->year_end && $this->year_start != $this->year_end) {
            return $this->year_start.'–'.$this->year_end;
        }

        return (string) ($this->year_start ?: $this->year_end ?: '');
    }
}
