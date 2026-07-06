<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'name',
        'photo_path',
        'status',
        'degree',
        'batch',
        'thesis_title',
        'publication_id',
        'co_supervisors',
        'start_year',
        'completion_year',
        'completed_at',
        'description',
        'profile_links',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'profile_links' => 'array',
        'completed_at' => 'date',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return config('academic.student_statuses.'.$this->status, ucfirst($this->status));
    }

    public function photoUrl(): ?string
    {
        return PublicStorage::url($this->resolvedPhotoPath());
    }

    public function resolvedPhotoPath(): ?string
    {
        $path = $this->photo_path;

        if (blank($path)) {
            return null;
        }

        if (is_array($path)) {
            $path = reset($path) ?: null;
        }

        if (blank($path)) {
            return null;
        }

        $path = (string) $path;

        if (str_starts_with($path, '[')) {
            $decoded = json_decode($path, true);

            if (is_array($decoded)) {
                $path = $decoded[0] ?? null;
            }
        }

        if (blank($path)) {
            return null;
        }

        return ltrim(str_replace(['\\', 'public/'], ['/', ''], $path), '/');
    }

    /** @return list<array{platform: string, label: string, url: string}> */
    public function profileLinksList(): array
    {
        $platforms = config('academic.student_profile_platforms', []);

        return collect($this->profile_links ?? [])
            ->map(function (array $link) use ($platforms): ?array {
                $url = trim((string) ($link['url'] ?? ''));

                if ($url === '') {
                    return null;
                }

                $platform = (string) ($link['platform'] ?? 'other');

                return [
                    'platform' => $platform,
                    'label' => $platforms[$platform] ?? ucfirst(str_replace('_', ' ', $platform)),
                    'url' => $url,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function completedDateLabel(): ?string
    {
        if ($this->completed_at) {
            return $this->completed_at->format('F j, Y');
        }

        if ($this->status === 'completed' && $this->completion_year) {
            return (string) $this->completion_year;
        }

        return null;
    }
}
