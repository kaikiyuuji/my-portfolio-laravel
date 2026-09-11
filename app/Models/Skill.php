<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Skill extends Model
{
    use HasTranslations;

    public const CATEGORIES = ['technical', 'interpersonal'];

    protected $fillable = [
        'title',
        'description',
        'category',
        'order',
        'is_visible',
    ];

    protected $hidden = ['seed_key'];

    protected $attributes = ['order' => 0, 'is_visible' => true];

    protected $casts = ['order' => 'integer', 'is_visible' => 'boolean'];

    public array $translatable = ['title', 'description'];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
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
