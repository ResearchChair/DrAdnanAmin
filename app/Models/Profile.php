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
        'orcid_id',
        'openalex_author_id',
        'orcid_synced_at',
    ];

    protected $casts = [
        'orcid_synced_at' => 'datetime',
    ];

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

    public static function current(): self
    {
        return static::query()->firstOrFail();
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
}
