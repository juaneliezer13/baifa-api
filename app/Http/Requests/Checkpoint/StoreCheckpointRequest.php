<?php

namespace App\Http\Requests\Checkpoint;

use App\Enums\GeneratorStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckpointRequest extends FormRequest
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
            'status' => ['required', Rule::in(GeneratorStatus::values())],
            'checkpoint_name' => ['required', 'string', 'min:3', 'max:150'],
            'event_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'nuevo estado del generador',
            'checkpoint_name' => 'nombre del punto de control',
            'event_date' => 'fecha y hora del evento',
            'notes' => 'observaciones',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'El nuevo estado del generador es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido en el sistema de tracking.',
            'checkpoint_name.required' => 'El nombre del punto de control o ubicación es obligatorio.',
            'checkpoint_name.min' => 'El nombre del punto de control debe tener al menos 3 caracteres.',
            'checkpoint_name.max' => 'El nombre del punto de control no puede exceder 150 caracteres.',
            'event_date.date' => 'La fecha y hora del evento debe ser una fecha válida.',
            'notes.max' => 'Las observaciones no pueden exceder 2000 caracteres.',
        ];
    }
}
