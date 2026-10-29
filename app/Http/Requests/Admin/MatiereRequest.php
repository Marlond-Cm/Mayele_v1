<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MatiereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'publie' => $this->boolean('publie'),
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
            'parcours' => $this->filled('parcours') ? trim($this->input('parcours')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'filiere_id' => ['required', 'exists:filieres,id'],
            'niveau' => ['required', Rule::in(['L1', 'L2', 'L3'])],
            'semestre' => ['required', 'integer', 'between:1,6'],
            'parcours' => ['nullable', 'string', 'max:120'],
            'nom' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('matieres', 'slug')->ignore($this->route('matiere')?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'ordre' => ['nullable', 'integer', 'between:0,999'],
            'publie' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'filiere_id.required' => 'Choisis une filière.',
            'niveau.required' => 'Choisis un niveau.',
            'semestre.required' => 'Choisis un semestre.',
            'nom.required' => 'Le nom de la matière est obligatoire.',
            'slug.unique' => 'Ce lien existe déjà pour une autre matière.',
        ];
    }
}