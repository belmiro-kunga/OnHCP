<?php

namespace App\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'video_url' => ['nullable','string','max:2048'],
            'duration_seconds' => ['nullable','integer','min:0'],
            'sort_index' => ['nullable','integer','min:0'],
        ];
    }
}
