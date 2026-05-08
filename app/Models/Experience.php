<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Experience extends Model
{
    use HasTranslations;

    protected $fillable = [
        'company',
        'role',
        'description',
        'start_date',
        'end_date',
        'order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'order' => 'integer',
    ];

    public array $translatable = [
        'company',
        'role',
        'description',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('start_date', 'desc');
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
