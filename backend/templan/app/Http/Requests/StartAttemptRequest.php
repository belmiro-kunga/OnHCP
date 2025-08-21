<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resume' => ['sometimes','boolean'],
        ];
    }
}
