<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebServiceRequest extends FormRequest
{
    /**
     * Only admins may update Web Service metadata.
     * (The route-level `can:admin` gate already enforces this; this is
     * defense in depth, consistent with the other admin requests.)
     */
    public function authorize(): bool
    {
        return $this->user('api')?->isAdmin() === true;
    }

    /**
     * Only `name` and `description` are updatable metadata.
     * The `code` is a technical identifier and is intentionally immutable.
     * The global ON/OFF status has its own dedicated endpoint
     * (`PATCH .../status`) and must not be mixed into metadata updates.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function prepareForValidation(): void
    {
        $name = $this->input('name');
        $description = $this->input('description');

        if (is_string($name)) {
            $this->merge(['name' => trim($name)]);
        }

        if (is_string($description)) {
            $this->merge(['description' => trim($description)]);
        }
    }
}
