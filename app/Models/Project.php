<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
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

    protected $appends = ['image_url'];

    protected $casts = [
        'order' => 'integer',
        'is_featured' => 'boolean',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image_path ? Storage::url($this->image_path) : null
        );
    }

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
