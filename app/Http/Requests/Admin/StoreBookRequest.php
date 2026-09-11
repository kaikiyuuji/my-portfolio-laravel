<?php

namespace App\Http\Requests\Admin;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Book::CATEGORIES)],
            'description' => ['nullable', 'string', 'max:5000'],
            'purchase_url' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'author' => 'autor',
            'category' => 'categoria',
            'description' => 'descrição',
            'purchase_url' => 'link de compra',
            'cover' => 'capa',
            'is_published' => 'publicação',
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_url.url' => 'Informe um link de compra válido começando com http:// ou https://.',
            'purchase_url.max' => 'O link de compra deve ter no máximo 2.048 caracteres.',
        ];
    }
}
