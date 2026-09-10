<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userParam = $this->route('user');
        $userId = $userParam instanceof User ? $userParam->id : $userParam;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'required', 'string', Rule::enum(UserRole::class)],
            'is_active' => ['sometimes', 'boolean'],
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
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required' => 'El cargo o rol es obligatorio.',
            'role.enum' => 'El rol seleccionado no es válido.',
        ];
    }

    /**
     * Configuración posterior del validador para reglas de negocio de usuarios.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userParam = $this->route('user');
            $targetUser = $userParam instanceof User ? $userParam : User::find($userParam);

            if ($targetUser && $targetUser->isClient()) {
                if ($this->has('role') && $this->input('role') !== UserRole::CLIENT->value) {
                    $validator->errors()->add('role', 'No se puede cambiar el rol a un usuario de tipo cliente.');
                }
            } elseif ($targetUser && ! $targetUser->isClient()) {
                if ($this->has('role') && $this->input('role') === UserRole::CLIENT->value) {
                    $validator->errors()->add('role', 'Los usuarios de tipo cliente solo pueden crearse a través del módulo de clientes.');
                }
            }
        });
    }
}
