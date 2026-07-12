<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitePageView extends Model
{
    protected $fillable = [
        'visitor_key',
        'path',
        'page_label',
        'country_code',
        'country_name',
        'is_new_visitor',
        'ip_hash',
    ];

    protected $casts = [
        'is_new_visitor' => 'boolean',
    ];
}
