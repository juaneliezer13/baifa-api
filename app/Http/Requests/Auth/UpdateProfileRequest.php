<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $merges = [];

        if ($this->has('email')) {
            $merges['email'] = strtolower(trim((string) $this->email));
        }

        if ($this->has('rif')) {
            $merges['rif'] = strtoupper(trim((string) $this->rif));
        }

        if ($this->has('office_phone') && $this->office_phone !== null) {
            $merges['office_phone'] = trim((string) $this->office_phone);
        }

        if ($this->has('contact_phone') && $this->contact_phone !== null) {
            $merges['contact_phone'] = trim((string) $this->contact_phone);
        }

        if (! empty($merges)) {
            $this->merge($merges);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $client = $user?->client;
        $phoneRegex = '/^(?:(?:\+?58[\s\-]?)?0?(?:2\d{2}|4(?:12|14|16|22|24|26))[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}|\+\d{1,3}[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{3,4}[\s\-]?\d{3,4})$/';

        return [
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'current_password' => ['required_with:password', 'nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'company_fiscal_name' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'company_short_name' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'rif' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^(?:[JGVECP]-\d{8,9}-\d|[VE]-\d{6,9})$/i',
                Rule::unique('clients', 'rif')->ignore($client?->id),
            ],
            'office_phone' => ['nullable', 'string', 'max:30', "regex:{$phoneRegex}"],
            'contact_phone' => ['nullable', 'string', 'max:30', "regex:{$phoneRegex}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre completo es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa una dirección de correo electrónico válida.',
            'email.regex' => 'El correo electrónico debe incluir un dominio válido (ej. usuario@empresa.com).',
            'email.unique' => 'Este correo electrónico ya está siendo utilizado por otra cuenta.',
            'current_password.required_with' => 'Debes ingresar tu contraseña actual para establecer una nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'company_fiscal_name.required' => 'La razón social de la empresa es obligatoria.',
            'company_fiscal_name.min' => 'La razón social debe tener al menos 3 caracteres.',
            'company_short_name.required' => 'El nombre corto o comercial es obligatorio.',
            'rif.required' => 'El RIF o Cédula fiscal es obligatorio.',
            'rif.regex' => 'Formato de RIF/Cédula inválido. Usa J-12345678-9 o V-12345678.',
            'rif.unique' => 'Ya existe otra empresa registrada con este número de RIF.',
            'office_phone.regex' => 'Teléfono de oficina inválido. Usa formato 0212-5551234 o +58 212-5551234.',
            'contact_phone.regex' => 'Teléfono de contacto inválido. Usa formato 0414-1234567 o +58 414-1234567.',
        ];
    }
}
