<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UniversiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'actif' => $this->boolean('actif'),
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:190'],
            'sigle' => ['nullable', 'string', 'max:30'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('universites', 'slug')->ignore($this->route('universite')?->id)],
            'ville' => ['nullable', 'string', 'max:120'],
            'type' => ['required', Rule::in(['public', 'prive'])],
            'actif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'université est obligatoire.',
            'slug.unique' => 'Ce lien existe déjà.',
            'type.required' => 'Choisis public ou privé.',
        ];
    }
}