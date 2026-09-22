<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserWebServicePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'is_enabled' => ['required', 'boolean'],
        ];
    }
}
