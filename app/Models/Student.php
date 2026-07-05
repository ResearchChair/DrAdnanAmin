<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name',
        'status',
        'degree',
        'thesis_title',
        'co_supervisors',
        'start_year',
        'completion_year',
        'description',
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

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getStatusLabelAttribute(): string
    {
        return config('academic.student_statuses.'.$this->status, ucfirst($this->status));
    }
}
