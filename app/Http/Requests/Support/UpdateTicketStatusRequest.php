<?php

namespace App\Http\Requests\Support;

use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo administradores y empleados pueden cambiar estatus o cancelar tickets.
        // El cliente no tiene autorización para cambiar estados ni cancelar.
        $user = $this->user();

        return $user && in_array($user->role?->value, ['admin', 'manager', 'employee'], true);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(TicketStatus::values())],
            'final_comment' => [
                'required_if:status,'.TicketStatus::FINISHED->value.','.TicketStatus::CANCELLED->value,
                'nullable',
                'string',
                'min:5',
                'max:2000',
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'nuevo estado del ticket',
            'final_comment' => 'comentario final de resolución o cancelación',
            'comment' => 'observaciones del cambio',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'El nuevo estado del ticket es obligatorio.',
            'status.in' => 'El estado indicado no es válido.',
            'final_comment.required_if' => 'Para cambiar el ticket a Finalizado o Cancelado es obligatorio ingresar un comentario final explicativo.',
            'final_comment.min' => 'El comentario final debe contener al menos 5 caracteres.',
            'final_comment.max' => 'El comentario final no puede exceder 2000 caracteres.',
            'comment.max' => 'Las observaciones no pueden exceder 1000 caracteres.',
        ];
    }
}
