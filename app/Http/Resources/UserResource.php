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
            'client_id' => $this->client?->id,
            'client' => $this->client ? [
                'id' => $this->client->id,
                'company_fiscal_name' => $this->client->company_fiscal_name,
                'company_short_name' => $this->client->company_short_name,
                'rif' => $this->client->rif,
                'office_phone' => $this->client->office_phone,
                'contact_name' => $this->client->contact_name,
                'contact_email' => $this->client->contact_email,
                'contact_phone' => $this->client->contact_phone,
                'is_active' => (bool) $this->client->is_active,
            ] : null,
        ];
    }
}
