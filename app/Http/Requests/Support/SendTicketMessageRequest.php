<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class SendTicketMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'message' => 'mensaje del chat',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'El mensaje no puede estar vacío.',
            'message.max' => 'El mensaje no puede exceder 5000 caracteres.',
        ];
    }
}
