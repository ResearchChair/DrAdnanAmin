<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingSession extends Model
{
    protected $fillable = [
        'title',
        'type',
        'event_name',
        'organization',
        'role',
        'year',
        'location',
        'description',
        'materials_url',
        'gallery_album_id',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function galleryAlbum(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return config('academic.training_types.'.$this->type, ucfirst($this->type));
    }
}
