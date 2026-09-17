<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class CheckCinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cin' => ['required', 'string', 'max:15'],
        ];
    }

    public function prepareForValidation(): void
    {
        if (is_string($this->input('cin'))) {
            $this->merge(['cin' => trim($this->input('cin'))]);
        }
    }
}
