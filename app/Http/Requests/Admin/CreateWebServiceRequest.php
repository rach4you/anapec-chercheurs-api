<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWebServiceRequest extends FormRequest
{
    /**
     * Only admins may create Web Services.
     * (The route-level `can:admin` gate already enforces this; this is
     * defense in depth, consistent with the other admin requests.)
     */
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('api_web_services', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'domain_codes' => ['sometimes', 'array'],
            'domain_codes.*' => ['string', 'max:60', 'distinct', 'exists:api_web_service_domains,code'],
        ];
    }

    public function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }

        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }

        if (is_string($this->input('description'))) {
            $this->merge(['description' => trim($this->input('description'))]);
        }

        $codes = $this->input('domain_codes');
        if (is_array($codes)) {
            $normalized = [];
            foreach ($codes as $code) {
                if (is_string($code)) {
                    $normalized[] = strtoupper(trim($code));
                }
            }
            $this->merge(['domain_codes' => $normalized]);
        }
    }
}
