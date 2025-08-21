<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSimuladoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:3',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|integer|exists:simulado_categories,id',
            'duration' => 'required|integer|min:60|max:28800', // Max 8 hours in seconds
            'min_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:50',
            'type' => 'required|string|max:50',
            'allow_navigation' => 'sometimes|boolean',
            'allow_save_progress' => 'sometimes|boolean',
            'show_feedback' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:active,archived',
            'questions' => 'sometimes|array|min:1|max:200',
            'questions.*.statement' => 'required_with:questions|string|max:1000|min:10',
            'questions.*.q_type' => [
                'nullable',
                Rule::in(['multiple_choice', 'true_false', 'essay', 'ordering', 'matching'])
            ],
            'questions.*.options' => 'required_if:questions.*.q_type,multiple_choice|array|min:2|max:6',
            'questions.*.options.*' => 'required|string|max:500',
            'questions.*.correct_answer' => 'required_with:questions|string|max:10',
            'questions.*.weight' => 'nullable|integer|min:1|max:10',
            'questions.*.difficulty' => 'nullable|string|in:easy,medium,hard',
            'questions.*.explanation' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título do simulado é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 3 caracteres.',
            'title.max' => 'O título não pode exceder 255 caracteres.',
            'duration.required' => 'A duração do simulado é obrigatória.',
            'duration.min' => 'A duração deve ser de pelo menos 60 segundos.',
            'duration.max' => 'A duração não pode exceder 8 horas (28800 segundos).',
            'min_score.required' => 'A nota mínima para aprovação é obrigatória.',
            'min_score.min' => 'A nota mínima não pode ser negativa.',
            'min_score.max' => 'A nota mínima não pode exceder 100.',
            'max_attempts.min' => 'O número mínimo de tentativas é 1.',
            'max_attempts.max' => 'O número máximo de tentativas é 50.',
            'type.required' => 'O tipo do simulado é obrigatório.',
            'start_date.after_or_equal' => 'A data de início não pode ser anterior a hoje.',
            'end_date.after' => 'A data de fim deve ser posterior à data de início.',
            'questions.min' => 'O simulado deve ter pelo menos 1 questão.',
            'questions.max' => 'O simulado não pode ter mais de 200 questões.',
            'questions.*.statement.required_with' => 'O texto da questão é obrigatório.',
            'questions.*.statement.min' => 'A questão deve ter pelo menos 10 caracteres.',
            'questions.*.statement.max' => 'A questão não pode exceder 1000 caracteres.',
            'questions.*.q_type.in' => 'Tipo de questão inválido.',
            'questions.*.options.required_if' => 'Questões de múltipla escolha devem ter opções.',
            'questions.*.options.min' => 'Questões de múltipla escolha devem ter pelo menos 2 opções.',
            'questions.*.options.max' => 'Questões de múltipla escolha não podem ter mais de 6 opções.',
            'questions.*.correct_answer.required_with' => 'A resposta correta é obrigatória.',
            'questions.*.weight.min' => 'O peso mínimo é 1.',
            'questions.*.weight.max' => 'O peso máximo é 10.',
            'questions.*.difficulty.in' => 'Nível de dificuldade inválido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descrição',
            'duration' => 'duração',
            'min_score' => 'nota mínima',
            'max_attempts' => 'máximo de tentativas',
            'type' => 'tipo',
            'questions' => 'questões',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize text fields
        if ($this->has('title')) {
            $this->merge(['title' => strip_tags($this->title)]);
        }
        
        if ($this->has('description')) {
            $this->merge(['description' => strip_tags($this->description)]);
        }
        
        if ($this->has('instructions')) {
            $this->merge(['instructions' => strip_tags($this->instructions)]);
        }
        
        // Set default values
        $this->merge([
            'allow_navigation' => $this->boolean('allow_navigation', true),
            'allow_save_progress' => $this->boolean('allow_save_progress', true),
            'show_feedback' => $this->boolean('show_feedback', true),
            'status' => $this->input('status', 'active'),
        ]);
    }
}