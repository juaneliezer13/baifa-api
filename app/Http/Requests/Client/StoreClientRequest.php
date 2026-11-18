<?php

namespace App\Http\Requests\Client;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::ADMIN, UserRole::MANAGER) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merges = [];

        if ($this->has('rif')) {
            $merges['rif'] = strtoupper(trim((string) $this->rif));
        }

        if ($this->has('contact_email')) {
            $merges['contact_email'] = strtolower(trim((string) $this->contact_email));
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
        $phoneRegex = '/^(?:(?:\+?58[\s\-]?)?0?(?:2\d{2}|4(?:12|14|16|22|24|26))[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}|\+\d{1,3}[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{3,4}[\s\-]?\d{3,4})$/';

        return [
            'company_fiscal_name' => ['required', 'string', 'max:255'],
            'company_short_name' => ['required', 'string', 'max:100'],
            'rif' => [
                'required',
                'string',
                'max:20',
                'unique:clients,rif',
                'regex:/^(?:[JGVECP]-\d{8,9}-\d|[VE]-\d{6,9})$/i',
            ],
            'office_phone' => ['nullable', 'string', 'max:30', "regex:{$phoneRegex}"],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => [
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/',
                'max:255',
                'unique:users,email',
            ],
            'contact_phone' => ['nullable', 'string', 'max:30', "regex:{$phoneRegex}"],
            'is_active' => ['sometimes', 'boolean'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_fiscal_name.required' => 'La razón social de la empresa es obligatoria.',
            'company_short_name.required' => 'El nombre corto de la empresa es obligatorio.',
            'rif.required' => 'El número de RIF o Cédula es obligatorio.',
            'rif.unique' => 'Ya existe una empresa registrada con este número de RIF o Cédula.',
            'rif.regex' => 'Formato inválido. Usa RIF (ej. J-12345678-9) o Cédula (ej. V-12345678).',
            'office_phone.regex' => 'Teléfono de oficina inválido. Usa un formato válido (ej. 0212-5551234 o +58 212-5551234).',
            'contact_name.required' => 'El nombre de la persona de contacto es obligatorio.',
            'contact_email.required' => 'El correo electrónico de contacto es obligatorio.',
            'contact_email.email' => 'El correo de contacto no tiene un formato válido.',
            'contact_email.regex' => 'Ingresa un correo electrónico válido (ej. contacto@empresa.com).',
            'contact_email.unique' => 'Ya existe un usuario registrado con este correo electrónico de contacto.',
            'contact_phone.regex' => 'Teléfono de contacto inválido. Usa un formato válido (ej. 0414-1234567 o +58 414-1234567).',
            'user_id.exists' => 'El usuario asociado especificado no existe.',
        ];
    }
}
