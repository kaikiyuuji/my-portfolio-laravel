<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeSetting extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id', 'location', 'phone', 'linkedin_url', 'website_url',
        'education', 'certifications', 'languages', 'interests',
    ];

    protected $casts = [
        'education' => 'array', 'certifications' => 'array',
        'languages' => 'array', 'interests' => 'array',
    ];
}
