<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'company' => ['required', 'array'],
            'company.pt' => ['required', 'string', 'max:255'],
            'company.en' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'array'],
            'role.pt' => ['required', 'string', 'max:255'],
            'role.en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.pt' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
