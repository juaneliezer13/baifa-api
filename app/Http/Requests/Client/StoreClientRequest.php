<?php

namespace App\Http\Requests\Client;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
        if ($this->has('rif')) {
            $this->merge([
                'rif' => strtoupper(trim((string) $this->rif)),
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
            'company_fiscal_name' => ['required', 'string', 'max:255'],
            'company_short_name' => ['required', 'string', 'max:100'],
            'rif' => [
                'required',
                'string',
                'max:20',
                'unique:clients,rif',
                'regex:/^[JGVEPjgvep]-\d{8,9}-\d$/',
            ],
            'office_phone' => ['nullable', 'string', 'max:50'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
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
            'company_fiscal_name.required' => 'La razón social de la empresa es obligatoria.',
            'company_short_name.required' => 'El nombre corto de la empresa es obligatorio.',
            'rif.required' => 'El número de RIF es obligatorio.',
            'rif.unique' => 'Ya existe una empresa registrada con este número de RIF.',
            'rif.regex' => 'El RIF debe tener un formato válido venezolano (ej. J-12345678-9).',
            'contact_name.required' => 'El nombre de la persona de contacto es obligatorio.',
            'contact_email.required' => 'El correo electrónico de contacto es obligatorio.',
            'contact_email.email' => 'El correo de contacto no tiene un formato válido.',
            'contact_email.unique' => 'Ya existe un usuario registrado con este correo electrónico de contacto.',
            'user_id.exists' => 'El usuario asociado especificado no existe.',
        ];
    }
}
