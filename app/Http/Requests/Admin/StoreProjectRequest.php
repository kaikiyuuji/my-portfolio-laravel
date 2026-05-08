<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'stack_ids' => ['nullable', 'array'],
            'stack_ids.*' => ['integer', 'exists:stacks,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }
}
