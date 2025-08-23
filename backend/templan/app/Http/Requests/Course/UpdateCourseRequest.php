<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');
        if (is_string($status)) {
            $s = strtolower(trim($status));
            $map = [
                'rascunho' => 'draft',
                'draft' => 'draft',
                'publicado' => 'published',
                'published' => 'published',
            ];
            if (isset($map[$s])) {
                $this->merge(['status' => $map[$s]]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes','required','string','max:255'],
            'description' => ['nullable','string'],
            'category_id' => ['nullable','exists:course_categories,id'],
            'status' => ['nullable', Rule::in(['draft','published'])],
            'sort_index' => ['nullable','integer','min:0'],
            'thumbnail' => ['nullable','image','max:5120'],
        ];
    }
}
