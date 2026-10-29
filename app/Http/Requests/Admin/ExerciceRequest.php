<?php

namespace App\Http\Requests\Admin;

use App\Models\Exercice;
use App\Services\AtelierTestsParser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExerciceRequest extends FormRequest
{
    private ?array $tests = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_gratuit' => $this->boolean('est_gratuit'),
            'publie' => $this->boolean('publie'),
            'slug' => $this->filled('slug') ? Str::slug($this->input('slug')) : null,
            'matiere_id' => $this->filled('matiere_id') ? $this->input('matiere_id') : null,
            'chapitre_id' => $this->filled('chapitre_id') ? $this->input('chapitre_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'filiere_id' => ['required', 'exists:filieres,id'],
            'matiere_id' => ['nullable', 'exists:matieres,id'],
            'chapitre_id' => ['nullable', 'exists:chapitres,id'],
            'titre' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('exercices', 'slug')->ignore($this->route('exercice')?->id)],
            'enonce' => ['required', 'string', 'max:20000'],
            'langage' => ['required', Rule::in(array_keys(Exercice::LANGAGES))],
            'code_depart' => ['nullable', 'string', 'max:20000'],
            'tests_texte' => ['required', 'string', 'max:60000'],
            'indice' => ['nullable', 'string', 'max:3000'],
            'solution' => ['nullable', 'string', 'max:20000'],
            'difficulte' => ['required', Rule::in(array_keys(Exercice::DIFFICULTES))],
            'ordre' => ['nullable', 'integer', 'between:0,999'],
            'est_gratuit' => ['boolean'],
            'publie' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $resultat = AtelierTestsParser::parse((string) $this->input('langage'), (string) $this->input('tests_texte'));

            foreach ($resultat['erreurs'] as $erreur) {
                $v->errors()->add('tests_texte', $erreur);
            }

            if ($resultat['erreurs'] === []) {
                $this->tests = $resultat['tests'];
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function testsParses(): array
    {
        return $this->tests ?? [];
    }

    public function messages(): array
    {
        return [
            'filiere_id.required' => 'Choisis la filière.',
            'titre.required' => 'Le titre est obligatoire.',
            'enonce.required' => 'L\'énoncé est obligatoire.',
            'langage.required' => 'Choisis le langage.',
            'tests_texte.required' => 'Ajoute au moins un test.',
            'slug.unique' => 'Ce lien existe déjà.',
        ];
    }
}