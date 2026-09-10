<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transforma el recurso de usuario a un array JSON estandarizado.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isActive = (bool) ($this->is_active ?? true);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'role_label' => $this->role?->label(),
            'is_active' => $isActive,
            'status' => $isActive ? 'active' : 'inactive',
            'status_label' => $isActive ? 'Activo' : 'Inactivo',
            'registered_date' => $this->created_at?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'is_self' => $request->user()?->id === $this->id,
        ];
    }
}
