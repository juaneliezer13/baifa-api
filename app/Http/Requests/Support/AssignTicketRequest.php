<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios internos (admin, manager, employee)
        $user = $this->user();

        return $user && in_array($user->role?->value, ['admin', 'manager', 'employee'], true);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Si viene nulo, significa que el usuario autenticado toma el ticket directamente (auto-asignación)
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'assigned_to_user_id' => 'operador asignado',
            'comment' => 'comentario de asignación',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assigned_to_user_id.exists' => 'El operador seleccionado no existe en el sistema.',
            'comment.max' => 'El comentario no puede exceder los 500 caracteres.',
        ];
    }
}
