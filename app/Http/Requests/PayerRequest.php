<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telephone' => preg_replace('/\s+/', '', (string) $this->input('telephone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('actif', true)],
            'telephone' => ['required', 'regex:/^\+?[0-9]{8,15}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'Choisis une formule.',
            'plan_id.exists' => 'Cette formule n\'est plus disponible.',
            'telephone.required' => 'Entre ton numéro Mobile Money.',
            'telephone.regex' => 'Numéro invalide (8 à 15 chiffres).',
        ];
    }
}