<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telephone' => preg_replace('/\s+/', '', (string) $this->input('telephone')),
            'code_parrain' => strtoupper(trim((string) $this->input('code_parrain'))) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'telephone' => ['required', 'regex:/^\+?[0-9]{8,15}$/', 'unique:users,telephone'],
            'universite_id' => ['required', Rule::exists('universites', 'id')->where('actif', true)],
            'filiere_id' => ['required', Rule::exists('filieres', 'id')->where('actif', true)],
            'niveau' => ['required', Rule::in(['L1', 'L2', 'L3'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'code_parrain' => ['nullable', 'string', Rule::exists('users', 'code_parrainage')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Entre ton nom complet.',
            'email.required' => 'Entre ton adresse e-mail.',
            'email.email' => 'Cette adresse e-mail est invalide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'telephone.required' => 'Entre ton numéro de téléphone.',
            'telephone.regex' => 'Numéro invalide (8 à 15 chiffres).',
            'telephone.unique' => 'Ce numéro est déjà utilisé.',
            'universite_id.required' => 'Choisis ton université.',
            'universite_id.exists' => 'Université invalide.',
            'filiere_id.required' => 'Choisis ta filière.',
            'filiere_id.exists' => 'Filière invalide.',
            'niveau.required' => 'Choisis ton niveau.',
            'niveau.in' => 'Niveau invalide.',
            'password.required' => 'Choisis un mot de passe.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'code_parrain.exists' => 'Ce code de parrainage n\'existe pas.',
        ];
    }
}