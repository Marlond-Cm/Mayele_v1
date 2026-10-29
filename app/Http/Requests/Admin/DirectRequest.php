<?php

namespace App\Http\Requests\Admin;

use App\Models\Matiere;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_gratuit' => $this->boolean('est_gratuit'),
            'matiere_id' => $this->filled('matiere_id') ? $this->input('matiere_id') : null,
            'animateur_id' => $this->filled('animateur_id') ? $this->input('animateur_id') : null,
            'replay_url' => $this->filled('replay_url') ? trim((string) $this->input('replay_url')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'filiere_id' => ['required', 'exists:filieres,id'],
            'matiere_id' => ['nullable', 'exists:matieres,id'],
            'animateur_id' => ['nullable', 'exists:users,id'],
            'debut_le' => ['required', 'date'],
            'duree_minutes' => ['required', 'integer', 'between:15,300'],
            'est_gratuit' => ['boolean'],
            'replay_url' => ['nullable', 'url', 'max:500'],
            'statut' => ['nullable', Rule::in(['planifie', 'annule'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('matiere_id')
                && ! Matiere::where('id', $this->input('matiere_id'))->where('filiere_id', $this->input('filiere_id'))->exists()) {
                $v->errors()->add('matiere_id', 'Cette matière n\'appartient pas à la filière choisie.');
            }

            if ($this->filled('animateur_id')
                && ! User::where('id', $this->input('animateur_id'))->whereIn('role', ['admin', 'tuteur'])->exists()) {
                $v->errors()->add('animateur_id', 'L\'animateur doit être un administrateur ou un tuteur.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre est obligatoire.',
            'filiere_id.required' => 'Choisis la filière.',
            'debut_le.required' => 'Choisis la date et l\'heure de début.',
            'debut_le.date' => 'Date de début invalide.',
            'duree_minutes.required' => 'Entre la durée.',
            'duree_minutes.between' => 'La durée doit être entre 15 et 300 minutes.',
            'replay_url.url' => 'Le lien du replay est invalide.',
        ];
    }
}