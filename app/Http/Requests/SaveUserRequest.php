<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $user = $this->route('user');
        $creating = ! $user instanceof User;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => [$creating ? 'required' : 'nullable', 'string', 'required_with:password'],
            'role' => ['required', Rule::in(User::AVAILABLE_ROLES)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'password.required_with' => 'Slaptažodis ir jo patvirtinimas turi sutapti.',
            'password.required' => 'Slaptažodis yra privalomas.',
            'password_confirmation.required_with' => 'Slaptažodis ir jo patvirtinimas turi sutapti.',
            'password_confirmation.required' => 'Slaptažodžio patvirtinimas yra privalomas.',
            'password.confirmed' => 'Slaptažodis ir jo patvirtinimas turi sutapti.',
        ];
    }
}
