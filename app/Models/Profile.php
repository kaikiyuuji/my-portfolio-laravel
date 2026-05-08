<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Profile extends Model
{
    use HasTranslations;

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

    public array $translatable = [
        'headline',
        'bio',
    ];

    public function toArray(): array
    {
        $array = parent::toArray();
        foreach ($this->getTranslatableAttributes() as $field) {
            $array[$field] = $this->getTranslations($field);
        }

        return $array;
    }
}
