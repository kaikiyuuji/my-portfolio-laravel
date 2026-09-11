<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:2048'],
            'website_url' => ['nullable', 'url:http,https', 'max:2048'],
            'education' => ['present', 'array', 'list', 'max:20'],
            'education.*' => ['required', 'array:institution,location,course,period,details'],
            'education.*.institution' => ['required', 'string', 'max:255'],
            'education.*.location' => ['nullable', 'string', 'max:255'],
            'education.*.course' => ['required', 'string', 'max:255'],
            'education.*.period' => ['nullable', 'string', 'max:255'],
            'education.*.details' => ['nullable', 'string', 'max:3000'],
            'certifications' => ['present', 'array', 'list', 'max:50'],
            'certifications.*' => ['required', 'string', 'max:255'],
            'languages' => ['present', 'array', 'list', 'max:20'],
            'languages.*' => ['required', 'array:name,level'],
            'languages.*.name' => ['required', 'string', 'max:100'],
            'languages.*.level' => ['required', 'string', 'max:100'],
            'interests' => ['present', 'array', 'list', 'max:30'],
            'interests.*' => ['required', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'location' => 'localidade', 'phone' => 'telefone',
            'linkedin_url' => 'link do LinkedIn', 'website_url' => 'link do portfólio',
            'education' => 'formação', 'education.*.institution' => 'instituição',
            'education.*.course' => 'curso', 'education.*.period' => 'período',
            'education.*.location' => 'localidade', 'education.*.details' => 'detalhes da formação',
            'certifications.*' => 'certificado', 'languages.*.name' => 'idioma',
            'languages.*.level' => 'nível', 'interests.*' => 'interesse',
        ];
    }
}
