<?php

namespace App\Http\Requests;

use App\Models\Annale;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeposerAnnaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matiere_id' => ['required', Rule::exists('matieres', 'id')->where('publie', true)],
            'universite_id' => ['required', Rule::exists('universites', 'id')->where('actif', true)],
            'annee' => ['required', 'integer', 'between:2000,'.date('Y')],
            'session' => ['required', Rule::in(['normale', 'rattrapage'])],
            'type' => ['required', Rule::in(['examen', 'partiel', 'td'])],
            'fichier_sujet' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'fichier_corrige' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $existe = Annale::query()
                ->where('matiere_id', $this->input('matiere_id'))
                ->where('universite_id', $this->input('universite_id'))
                ->where('annee', $this->input('annee'))
                ->where('session', $this->input('session'))
                ->where('type', $this->input('type'))
                ->whereIn('statut', ['en_attente', 'valide'])
                ->exists();

            if ($existe) {
                $v->errors()->add('matiere_id', 'Cette annale existe déjà ou est en cours de validation.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'matiere_id.required' => 'Choisis la matière.',
            'matiere_id.exists' => 'Matière invalide.',
            'universite_id.required' => 'Choisis l\'université.',
            'universite_id.exists' => 'Université invalide.',
            'annee.required' => 'Entre l\'année de l\'examen.',
            'annee.between' => 'Année invalide.',
            'session.required' => 'Choisis la session.',
            'type.required' => 'Choisis le type de document.',
            'fichier_sujet.required' => 'Ajoute le sujet en PDF.',
            'fichier_sujet.mimes' => 'Le sujet doit être un PDF.',
            'fichier_sujet.max' => 'Le PDF ne doit pas dépasser 10 Mo.',
            'fichier_sujet.uploaded' => 'Le PDF n\'a pas pu être envoyé (fichier trop lourd ?).',
            'fichier_corrige.mimes' => 'Le corrigé doit être un PDF.',
            'fichier_corrige.max' => 'Le PDF ne doit pas dépasser 10 Mo.',
        ];
    }
}