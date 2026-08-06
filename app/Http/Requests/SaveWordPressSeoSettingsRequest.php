<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWordPressSeoSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'administrator'; }

    public function rules(): array
    {
        return [
            'slug_conflicts' => ['required', Rule::in(['overwrite', 'skip', 'generate'])],
            'meta_title' => ['nullable', 'boolean'],
            'meta_description' => ['nullable', 'boolean'],
            'canonical_url' => ['nullable', 'boolean'],
            'redirect_handling' => ['required', Rule::in(['none', 'automatic'])],
        ];
    }

    public function seoOptions(): array
    {
        $values = $this->validated();
        foreach (['meta_title', 'meta_description', 'canonical_url'] as $key) $values[$key] = $this->boolean($key);
        return $values;
    }
}
