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

        if ($this->has('email')) {
            $merges['email'] = strtolower(trim((string) $this->email));
        }

        if ($this->has('phone') && $this->phone !== null) {
            $merges['phone'] = trim((string) $this->phone);
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
                'regex:/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/',
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
                'regex:/^(?:[JGVECP]-\d{8,9}-\d|[VE]-\d{6,9})$/i',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^(?:(?:\+?58[\s\-]?)?0?(?:2\d{2}|4(?:12|14|16|22|24|26))[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}|\+\d{1,3}[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{3,4}[\s\-]?\d{3,4})$/',
            ],
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
            'email.regex' => 'Ingresa un correo electrónico válido (ej. usuario@empresa.com).',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado en el sistema.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'company_fiscal_name.required' => 'La razón social de la empresa es obligatoria.',
            'rif.required' => 'El número de RIF o Cédula es obligatorio.',
            'rif.unique' => 'Ya existe una empresa o cliente registrado con este número de RIF o Cédula.',
            'rif.regex' => 'Formato inválido. Usa RIF (ej. J-12345678-9) o Cédula (ej. V-12345678).',
            'phone.regex' => 'Teléfono inválido. Usa un formato válido (ej. 0414-1234567, 0212-5551234 o +58 414-1234567).',
        ];
    }
}
