<?php

namespace App\Http\Requests\Admin;

class UpdateBookRequest extends StoreBookRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'remove_cover' => ['sometimes', 'boolean'],
        ];
    }
}
