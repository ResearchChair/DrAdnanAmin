<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDraft extends Model
{
    public const TYPE_COVER_LETTER = 'cover_letter';

    public const TYPE_RESEARCH_PROPOSAL = 'research_proposal';

    public const TYPE_TAILORED_CV = 'tailored_cv';

    public const TYPES = [
        self::TYPE_COVER_LETTER => 'Cover letter',
        self::TYPE_RESEARCH_PROPOSAL => 'Research proposal',
        self::TYPE_TAILORED_CV => 'Personalized CV',
    ];

    protected $fillable = [
        'user_id',
        'document_type',
        'position_title',
        'institution',
        'tone',
        'job_text',
        'extra_notes',
        'publication_ids',
        'options',
        'output_markdown',
        'model',
        'provider',
        'prompt_tokens',
        'completion_tokens',
    ];

    protected $casts = [
        'publication_ids' => 'array',
        'options' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
            'openai' => 'OpenAI',
            'groq' => 'Groq',
            default => $this->provider ? ucfirst($this->provider) : 'n/a',
        };
    }

    public function downloadBasename(): string
    {
        $bits = array_filter([
            str_replace('_', '-', $this->document_type),
            $this->institution ? \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit($this->institution, 40, '')) : null,
            $this->created_at?->format('Y-m-d-His'),
        ]);

        return implode('-', $bits) ?: 'application-draft';
    }
}
