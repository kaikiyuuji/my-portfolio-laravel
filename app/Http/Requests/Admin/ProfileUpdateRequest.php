<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Currently checks that the user is authenticated. When roles/policies
     * are added, replace with a Gate::allows() or policy check.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules for updating the portfolio profile.
     *
     * NOTE: This validates the PORTFOLIO Profile model, not the admin User model.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'resume_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
