<?php

namespace App\Http\Requests\Generator;

use App\Enums\GeneratorStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGeneratorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::ADMIN, UserRole::MANAGER, UserRole::EMPLOYEE) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('serial_number')) {
            $this->merge([
                'serial_number' => strtoupper(trim((string) $this->serial_number)),
            ]);
        }

        if (! $this->has('status') || empty($this->status)) {
            $this->merge([
                'status' => GeneratorStatus::WAREHOUSE->value,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'serial_number' => ['required', 'string', 'max:100', 'unique:generators,serial_number'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'name' => ['nullable', 'string', 'max:150'],
            'model' => ['required', 'string', 'max:150'],
            'capacity_kva' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'status' => ['nullable', 'string', Rule::in(GeneratorStatus::values())],
            'estimated_arrival_date' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'photo_path' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Mensajes de validación personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'serial_number.required' => 'El número de serial de fábrica es obligatorio.',
            'serial_number.unique' => 'Ya existe un generador registrado con este número de serial.',
            'serial_number.max' => 'El serial no puede superar los 100 caracteres.',
            'client_id.exists' => 'El cliente asignado seleccionado no existe en el directorio fiscal.',
            'model.required' => 'El modelo o tipo de generador es obligatorio.',
            'model.max' => 'El modelo no puede superar los 150 caracteres.',
            'capacity_kva.numeric' => 'La capacidad en kVA debe ser un número válido.',
            'capacity_kva.min' => 'La capacidad en kVA no puede ser negativa.',
            'status.in' => 'El estado seleccionado no es válido.',
            'estimated_arrival_date.date' => 'La fecha estimada de llegada (ETA) debe ser una fecha válida.',
            'photo.image' => 'El archivo adjunto debe ser una imagen válida (JPEG, PNG o WebP).',
            'photo.max' => 'La imagen no puede exceder 5 MB de tamaño.',
        ];
    }
}
