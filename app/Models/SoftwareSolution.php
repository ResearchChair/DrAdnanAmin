<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SoftwareSolution extends Model
{
    protected $fillable = [
        'name',
        'organization',
        'tagline',
        'type',
        'year',
        'description',
        'tech_stack',
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function getTypeLabelAttribute(): string
    {
        return config('academic.software_solution_types.'.$this->type, ucfirst(str_replace('_', ' ', (string) $this->type)));
    }

    public function logoUrl(): ?string
    {
        return PublicStorage::url($this->logo_path);
    }

    public function techStackList(): array
    {
        return collect(preg_split('/[,;|]+/', (string) $this->tech_stack) ?: [])
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
