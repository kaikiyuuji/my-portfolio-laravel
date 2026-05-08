<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = [
        'platform',
        'icon_slug',
        'color',
        'url',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}
