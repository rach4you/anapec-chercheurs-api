<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignWebServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'web_services' => ['required', 'array', 'min:1'],
            'web_services.*.code' => ['required', 'string', 'max:60', 'exists:api_web_services,code'],
            'web_services.*.enabled' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The API normalizes a duplicate code by keeping the last occurrence.
     */
    public function prepareForValidation(): void
    {
        $items = $this->input('web_services', []);

        $deduped = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['code'])) {
                $deduped[$item['code']] = $item;
            }
        }

        $this->merge(['web_services' => array_values($deduped)]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('web_services', []) as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $code = $item['code'] ?? null;

                if (! $code) {
                    continue;
                }

                $webService = \App\Models\WebService::query()->where('code', $code)->first();

                if (! $webService) {
                    $validator->errors()->add('web_services', "Web Service '{$code}' does not exist.");

                    continue;
                }

                if (! $webService->is_active) {
                    $validator->errors()->add('web_services', "Web Service '{$code}' is not active.");
                }
            }
        });
    }
}
