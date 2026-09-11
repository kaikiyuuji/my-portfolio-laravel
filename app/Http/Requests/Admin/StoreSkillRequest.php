<?php

namespace App\Http\Requests\Admin;

use App\Models\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'array:pt,en'],
            'title.pt' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array:pt,en'],
            'description.pt' => ['nullable', 'string', 'max:1000'],
            'description.en' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', Rule::in(Skill::CATEGORIES)],
            'order' => ['sometimes', 'required', 'integer', 'between:0,9999'],
            'is_visible' => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'title.pt' => 'título em português',
            'title.en' => 'título em inglês',
            'description' => 'descrição',
            'description.pt' => 'descrição em português',
            'description.en' => 'descrição em inglês',
            'category' => 'categoria',
            'order' => 'ordem',
            'is_visible' => 'visibilidade',
        ];
    }
}
