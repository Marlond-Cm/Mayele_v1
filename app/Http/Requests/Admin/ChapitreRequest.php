<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ChapitreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_gratuit' => $this->boolean('est_gratuit'),
            'publie' => $this->boolean('publie'),
            'supprimer_pdf' => $this->boolean('supprimer_pdf'),
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
        ]);
    }

    public function rules(): array
    {
        $chapitre = $this->route('chapitre');
        $matiereId = $this->route('matiere')?->id ?? $chapitre?->matiere_id;

        return [
            'titre' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:190',
                Rule::unique('chapitres', 'slug')->where('matiere_id', $matiereId)->ignore($chapitre?->id)],
            'contenu' => ['nullable', 'string', 'max:200000'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'fichier_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'supprimer_pdf' => ['boolean'],
            'duree_minutes' => ['nullable', 'integer', 'between:0,600'],
            'ordre' => ['nullable', 'integer', 'between:0,999'],
            'est_gratuit' => ['boolean'],
            'publie' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre du chapitre est obligatoire.',
            'slug.unique' => 'Ce lien existe déjà dans cette matière.',
            'video_url.url' => 'Le lien de la vidéo est invalide.',
            'fichier_pdf.mimes' => 'Le fichier doit être un PDF.',
            'fichier_pdf.max' => 'Le PDF ne doit pas dépasser 20 Mo.',
            'fichier_pdf.uploaded' => 'Le PDF n\'a pas pu être envoyé (taille limitée par PHP).',
        ];
    }
}