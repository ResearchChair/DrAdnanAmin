<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'credentials',
        'title',
        'affiliation',
        'secondary_affiliation',
        'email',
        'phone',
        'whatsapp',
        'location',
        'tagline',
        'bio_html',
        'research_interests',
        'photo_path',
        'cv_path',
        'cv_label',
        'orcid_id',
        'openalex_author_id',
        'orcid_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'orcid_synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Profile $profile) {
            if ($profile->is_active && $profile->wasChanged('is_active')) {
                static::query()
                    ->whereKeyNot($profile->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function current(): self
    {
        return static::query()->active()->first()
            ?? static::query()->firstOrFail();
    }

    public function citationStats(): HasOne
    {
        return $this->hasOne(CitationStat::class);
    }

    public function academicProfiles(): HasMany
    {
        return $this->hasMany(AcademicProfile::class)->orderBy('sort_order');
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)->orderBy('sort_order');
    }

    public function whatsappUrl(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->whatsapp);

        return $digits ? 'https://wa.me/'.$digits : null;
    }

    public function photoUrl(): ?string
    {
        return PublicStorage::url($this->photo_path);
    }

    public function hasCv(): bool
    {
        return filled($this->cv_path) && PublicStorage::exists($this->cv_path);
    }
}
