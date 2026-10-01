<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('api_users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', 'string', Rule::in(User::allowedRoles())],
            'is_active' => ['sometimes', 'boolean'],
            'access_scope' => ['sometimes', 'string', Rule::in(User::accessScopes())],
        ];
    }

    /**
     * A normal user cannot create another admin. The API defaults role to "user"
     * unless the requesting admin explicitly passes "admin".
     */
    public function prepareForValidation(): void
    {
        if (! $this->filled('role')) {
            $this->merge(['role' => User::ROLE_USER]);
        }

        if (! $this->filled('access_scope')) {
            $this->merge(['access_scope' => User::ACCESS_SCOPE_SELECTED]);
        }
    }

    public function messages(): array
    {
        return [
            'role.in' => 'The role must be one of: '.implode(', ', User::allowedRoles()).'.',
            'access_scope.in' => 'The access scope must be one of: '.implode(', ', User::accessScopes()).'.',
        ];
    }
}
