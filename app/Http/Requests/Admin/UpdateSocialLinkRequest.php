<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', 'max:255'],
            'icon_slug' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
            'url' => ['required', 'url', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
