<?php

namespace App\Http\Requests\Admin;

use App\Services\QuizParser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
{
    private ?array $questions = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_gratuit' => $this->boolean('est_gratuit'),
            'publie' => $this->boolean('publie'),
        ]);
    }

    public function rules(): array
    {
        return [
            'matiere_id' => ['required', 'exists:matieres,id'],
            'titre' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points_par_question' => ['required', 'integer', 'between:1,100'],
            'questions' => ['required', 'string', 'max:100000'],
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

            $resultat = QuizParser::parse((string) $this->input('questions'));

            foreach ($resultat['erreurs'] as $erreur) {
                $v->errors()->add('questions', $erreur);
            }

            if ($resultat['erreurs'] === []) {
                $this->questions = $resultat['questions'];
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function questionsParsees(): array
    {
        return $this->questions ?? [];
    }

    public function messages(): array
    {
        return [
            'matiere_id.required' => 'Choisis la matière.',
            'titre.required' => 'Le titre du quiz est obligatoire.',
            'points_par_question.required' => 'Entre les points par bonne réponse.',
            'points_par_question.between' => 'Les points doivent être entre 1 et 100.',
            'questions.required' => 'Ajoute au moins une question.',
        ];
    }
}