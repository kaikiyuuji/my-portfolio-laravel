<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Project extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'repository_url',
        'demo_url',
        'order',
        'is_featured',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_featured' => 'boolean',
    ];

    public array $translatable = [
        'title',
        'description',
    ];

    public function stacks(): BelongsToMany
    {
        return $this->belongsToMany(Stack::class, 'project_technologies')
            ->withTimestamps();
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
