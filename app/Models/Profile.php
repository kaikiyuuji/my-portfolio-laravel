<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * This is the PORTFOLIO profile (singleton) — NOT the admin User model.
     * Contains public-facing data: name, headline, bio, contact email, avatar, resume.
     */
    protected $fillable = [
        'name',
        'headline',
        'bio',
        'email',
        'avatar_path',
        'resume_url',
    ];
}
