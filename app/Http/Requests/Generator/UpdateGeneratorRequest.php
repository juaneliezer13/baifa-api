<?php

namespace App\Http\Requests\Generator;

use App\Enums\GeneratorStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::ADMIN, UserRole::MANAGER, UserRole::EMPLOYEE) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('serial_number')) {
            $this->merge([
                'serial_number' => strtoupper(trim((string) $this->serial_number)),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $generator = $this->route('generator');
        $generatorId = $generator instanceof \App\Models\Generator ? $generator->id : $generator;

        return [
            'serial_number' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[A-Z0-9][A-Z0-9\-_\/]{2,99}$/',
                Rule::unique('generators', 'serial_number')->ignore($generatorId),
            ],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'name' => ['nullable', 'string', 'max:150'],
            'model' => ['sometimes', 'required', 'string', 'min:3', 'max:150'],
            'capacity_kva' => ['nullable', 'numeric', 'gt:0', 'max:99999'],
            'status' => ['sometimes', 'required', 'string', Rule::in(GeneratorStatus::values())],
            'estimated_arrival_date' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'photo_path' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'serial_number.required' => 'El número de serial de fábrica es obligatorio.',
            'serial_number.min' => 'El serial debe tener al menos 3 caracteres.',
            'serial_number.regex' => 'El serial solo puede contener letras, números y guiones (ej. GEN-2026-0041).',
            'serial_number.unique' => 'Ya existe un generador registrado con este número de serial.',
            'serial_number.max' => 'El serial no puede superar los 100 caracteres.',
            'client_id.exists' => 'El cliente asignado seleccionado no existe en el directorio fiscal.',
            'model.required' => 'El modelo o tipo de generador es obligatorio.',
            'model.min' => 'El modelo del generador debe tener al menos 3 caracteres.',
            'model.max' => 'El modelo no puede superar los 150 caracteres.',
            'capacity_kva.numeric' => 'La capacidad en kVA debe ser un número válido.',
            'capacity_kva.gt' => 'La capacidad en kVA debe ser mayor a 0.',
            'capacity_kva.max' => 'La capacidad en kVA no puede superar 99,999 kVA.',
            'status.in' => 'El estado seleccionado no es válido.',
            'estimated_arrival_date.date' => 'La fecha estimada de llegada (ETA) debe ser una fecha válida.',
            'photo.image' => 'El archivo adjunto debe ser una imagen válida (JPEG, PNG o WebP).',
            'photo.mimes' => 'Solo se admiten fotografías en formato JPEG, PNG o WebP.',
            'photo.max' => 'La imagen no puede exceder 5 MB de tamaño.',
        ];
    }
}
