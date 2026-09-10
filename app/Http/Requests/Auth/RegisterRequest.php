<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Preparar los datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        $merges = [];

        if ($this->has('rif')) {
            $merges['rif'] = strtoupper(trim((string) $this->rif));
        }

        if ($this->has('company_name') && ! $this->has('company_fiscal_name')) {
            $merges['company_fiscal_name'] = trim((string) $this->company_name);
        }

        if ($this->has('razon_social') && ! $this->has('company_fiscal_name')) {
            $merges['company_fiscal_name'] = trim((string) $this->razon_social);
        }

        if (! empty($merges)) {
            $this->merge($merges);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
                'unique:clients,contact_email',
            ],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'company_fiscal_name' => ['required', 'string', 'max:255'],
            'company_short_name' => ['nullable', 'string', 'max:100'],
            'rif' => [
                'required',
                'string',
                'max:20',
                'unique:clients,rif',
                'regex:/^[JGVEPjgvep]-\d{8,9}-\d$/',
            ],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Mensajes de validación en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre completo de contacto es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado en el sistema.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'company_fiscal_name.required' => 'La razón social de la empresa es obligatoria.',
            'rif.required' => 'El número de RIF es obligatorio.',
            'rif.unique' => 'Ya existe una empresa registrada con este número de RIF.',
            'rif.regex' => 'El RIF debe tener un formato válido venezolano (ej. J-12345678-9).',
        ];
    }
}
