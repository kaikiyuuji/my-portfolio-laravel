<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    public const CATEGORIES = ['academic', 'personal'];

    protected $fillable = [
        'title',
        'author',
        'category',
        'description',
        'purchase_url',
        'cover_path',
        'is_published',
    ];

    protected $attributes = ['is_published' => true];

    protected $appends = ['cover_url'];

    protected $casts = ['is_published' => 'boolean'];

    protected function coverUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->cover_path ? Storage::url($this->cover_path) : null
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
