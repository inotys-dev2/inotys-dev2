<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandeCeremonieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'   => ['required','string','max:120'],
            'paroisse_id' => ['required','integer','exists:paroisse,id'],

            'start'   => ['required','date_format:Y-m-d H:i','after:now'],
            'end'     => ['required','date_format:Y-m-d H:i','after:start'],

            'special_request' => ['nullable','string','max:1000'],
            'contact_family_name'  => ['nullable','string','max:120'],
            'contact_family_phone' => ['nullable','string','max:30','regex:/^[0-9+().\-\s]{6,}$/'],

            'status' => ['nullable', Rule::in(['draft','confirmed','cancelled'])],
            'website' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim(strip_tags((string)$this->input('title'))),
            'special_request' => trim(strip_tags((string)$this->input('special_request'))),
            'contact_family_name' => trim(strip_tags((string)$this->input('contact_family_name'))),
            'contact_family_phone' => trim((string)$this->input('contact_family_phone')),
        ]);
    }

    public function messages(): array
    {
        return [
            'start.after' => "L'heure de début doit être dans le futur.",
            'end.after'   => "L'heure de fin doit être postérieure à l'heure de début.",
        ];
    }
}
