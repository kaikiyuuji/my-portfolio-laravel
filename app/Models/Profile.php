<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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

    protected $appends = ['avatar_url'];

    public array $translatable = [
        'headline',
        'bio',
    ];

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->avatar_path ? Storage::url($this->avatar_path) : null
        );
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        foreach ($this->getTranslatableAttributes() as $field) {
            $array[$field] = $this->getTranslations($field);
        }

        return $array;
    }
}
