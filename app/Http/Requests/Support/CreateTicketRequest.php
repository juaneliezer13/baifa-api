<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados con rol cliente (o cualquier usuario autenticado)
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'título de la consulta',
            'category' => 'área o categoría',
            'description' => 'detalles de la consulta',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título de la consulta es obligatorio para generar el ticket.',
            'title.min' => 'El título debe contener al menos 3 caracteres.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'description.max' => 'Los detalles no pueden exceder 3000 caracteres.',
        ];
    }
}
