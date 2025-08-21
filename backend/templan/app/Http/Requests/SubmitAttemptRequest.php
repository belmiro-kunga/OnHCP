<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitAttemptRequest extends FormRequest
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
            'answers' => 'sometimes|array',
            'answers.*' => 'required|string|max:10',
            'timeSpent' => 'sometimes|integer|min:0|max:86400', // Max 24 hours
            'clientTimestamp' => 'sometimes|date',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'answers.array' => 'As respostas devem ser fornecidas como um array.',
            'answers.*.required' => 'Cada resposta é obrigatória.',
            'answers.*.string' => 'Cada resposta deve ser uma string.',
            'answers.*.max' => 'Cada resposta não pode exceder 10 caracteres.',
            'timeSpent.integer' => 'O tempo gasto deve ser um número inteiro.',
            'timeSpent.min' => 'O tempo gasto não pode ser negativo.',
            'timeSpent.max' => 'O tempo gasto não pode exceder 24 horas.',
            'clientTimestamp.date' => 'O timestamp do cliente deve ser uma data válida.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'answers' => 'respostas',
            'timeSpent' => 'tempo gasto',
            'clientTimestamp' => 'timestamp do cliente',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize answers to prevent injection
        if ($this->has('answers') && is_array($this->answers)) {
            $sanitizedAnswers = [];
            foreach ($this->answers as $key => $value) {
                $sanitizedAnswers[filter_var($key, FILTER_SANITIZE_STRING)] = 
                    filter_var($value, FILTER_SANITIZE_STRING);
            }
            $this->merge(['answers' => $sanitizedAnswers]);
        }
    }
}
