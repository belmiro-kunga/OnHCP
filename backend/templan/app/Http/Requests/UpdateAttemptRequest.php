<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currentQuestion' => ['sometimes','integer','min:0'],
            'answers' => ['sometimes','array'],
            'timeRemaining' => ['sometimes','integer','min:0'],
        ];
    }
}
