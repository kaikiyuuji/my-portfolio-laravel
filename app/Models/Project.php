<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
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

    public function stacks(): BelongsToMany
    {
        return $this->belongsToMany(Stack::class, 'project_technologies')
            ->withTimestamps();
    }
}
