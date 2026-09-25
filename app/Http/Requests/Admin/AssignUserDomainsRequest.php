<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserDomainsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'domains' => ['required', 'array', 'min:1'],
            'domains.*' => ['string', 'max:60', 'exists:api_web_service_domains,code'],
        ];
    }

    public function prepareForValidation(): void
    {
        $domains = $this->input('domains');

        if (is_array($domains)) {
            $deduped = [];
            foreach ($domains as $code) {
                if (is_string($code)) {
                    $deduped[trim($code)] = trim($code);
                }
            }
            $this->merge(['domains' => array_values($deduped)]);
        }
    }
}
