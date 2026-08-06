<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmWordPressImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'administrator';
    }

    public function rules(): array
    {
        return [
            'confirmed' => ['accepted'],
            'confirmation_phrase' => ['required', Rule::in(['IMPORTUOTI'])],
            'test_limit' => [Rule::excludeIf(! app()->environment(['local', 'testing'])), 'nullable', Rule::in([3, 5])],
        ];
    }
}
