<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    protected $fillable = [
        'title',
        'type',
        'status',
        'year',
        'venue',
        'publisher',
        'authors',
        'doi',
        'url',
        'pdf_url',
        'abstract',
        'citation_count',
        'external_id_orcid',
        'external_id_openalex',
        'featured',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function collaborators(): HasMany
    {
        return $this->hasMany(PublicationCollaborator::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return config('academic.publication_types.'.$this->type, ucfirst($this->type));
    }

    public function getStatusLabelAttribute(): string
    {
        return config('academic.publication_statuses.'.$this->status, ucfirst(str_replace('_', ' ', (string) $this->status)));
    }

    public function getDoiUrlAttribute(): ?string
    {
        if (! $this->doi) {
            return null;
        }

        $doi = str_starts_with($this->doi, 'http') ? $this->doi : 'https://doi.org/'.$this->doi;

        return $doi;
    }

    public function primaryUrl(): ?string
    {
        return $this->doi_url ?? $this->url ?? $this->pdf_url;
    }

    /**
     * Short recommend line for peer-review comments: Title (Year). Link
     */
    public function toShortCitation(): string
    {
        $title = trim((string) $this->title);
        $year = $this->year ? (string) $this->year : null;
        $line = $year ? "{$title} ({$year})." : "{$title}.";

        $link = $this->primaryUrl();
        if ($link) {
            $line .= ' '.$link;
        }

        return $line;
    }

    public function resolvedPublisher(): string
    {
        return \App\Support\PublicationSummary::inferPublisher($this->publisher, $this->venue, $this->doi);
    }
}
