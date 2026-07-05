<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicProfile extends Model
{
    protected $fillable = [
        'profile_id',
        'platform',
        'label',
        'url',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function getPlatformLabelAttribute(): string
    {
        return config('academic.academic_platforms.'.$this->platform, $this->label ?? $this->platform);
    }
}
