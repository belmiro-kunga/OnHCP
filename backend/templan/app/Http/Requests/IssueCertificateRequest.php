<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'simuladoId' => ['required','integer','min:1'],
            'attemptId' => ['required','string'],
        ];
    }
}
