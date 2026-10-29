<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creation = $this->isMethod('post');

        return [
            'matiere_id' => ['required', 'exists:matieres,id'],
            'universite_id' => ['required', 'exists:universites,id'],
            'annee' => ['required', 'integer', 'between:2000,'.((int) date('Y') + 1)],
            'session' => ['required', Rule::in(['normale', 'rattrapage'])],
            'type' => ['required', Rule::in(['examen', 'partiel', 'td'])],
            'fichier_sujet' => [$creation ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:20480'],
            'fichier_corrige' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'statut' => ['nullable', Rule::in(['en_attente', 'valide', 'refuse'])],
        ];
    }

    public function messages(): array
    {
        return [
            'matiere_id.required' => 'Choisis la matière.',
            'universite_id.required' => 'Choisis l\'université.',
            'annee.required' => 'Entre l\'année.',
            'annee.between' => 'Année invalide.',
            'fichier_sujet.required' => 'Ajoute le sujet en PDF.',
            'fichier_sujet.mimes' => 'Le sujet doit être un PDF.',
            'fichier_corrige.mimes' => 'Le corrigé doit être un PDF.',
            'fichier_sujet.max' => 'Le PDF ne doit pas dépasser 20 Mo.',
            'fichier_corrige.max' => 'Le PDF ne doit pas dépasser 20 Mo.',
        ];
    }
}