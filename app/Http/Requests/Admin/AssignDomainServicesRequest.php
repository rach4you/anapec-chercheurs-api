<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignDomainServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'web_services' => ['required', 'array', 'min:1'],
            'web_services.*' => ['string', 'max:60', 'exists:api_web_services,code'],
        ];
    }

    public function prepareForValidation(): void
    {
        $services = $this->input('web_services');

        if (is_array($services)) {
            $deduped = [];
            foreach ($services as $code) {
                if (is_string($code)) {
                    $deduped[trim($code)] = trim($code);
                }
            }
            $this->merge(['web_services' => array_values($deduped)]);
        }
    }
}
