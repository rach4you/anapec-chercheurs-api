<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ToggleWebServiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $isActive = $this->input('is_active');

        if (is_string($isActive)) {
            $this->merge(['is_active' => filter_var($isActive, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
