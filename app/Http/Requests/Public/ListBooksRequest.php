<?php

namespace App\Http\Requests\Public;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ListBooksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $search = $this->query('search');
        $category = $this->query('category');

        $this->merge([
            'search' => is_string($search) ? Str::substr(trim($search), 0, 120) : '',
            'category' => in_array($category, Book::CATEGORIES, true) ? $category : '',
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(Book::CATEGORIES)],
        ];
    }

    public function filters(): array
    {
        return [
            'search' => $this->validated('search') ?? '',
            'category' => $this->validated('category') ?? '',
        ];
    }
}
