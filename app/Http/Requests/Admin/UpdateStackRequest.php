<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $stackId = $this->route('stack')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'icon_slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stacks', 'icon_slug')->ignore($stackId),
            ],
            'color' => ['nullable', 'string', 'max:32'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }
}
