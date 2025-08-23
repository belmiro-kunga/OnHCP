<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('course_categories', 'name')->ignore($categoryId)
            ],
            'description' => 'nullable|string|max:1000',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('course_categories', 'slug')->ignore($categoryId)
            ],
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_index' => 'integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.unique' => 'Já existe uma categoria com este nome.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#RRGGBB).',
            'slug.unique' => 'Já existe uma categoria com este slug.',
        ];
    }
}