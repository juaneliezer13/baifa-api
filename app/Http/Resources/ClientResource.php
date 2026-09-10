<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transforma el recurso de cliente a un array JSON estandarizado.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isActive = (bool) ($this->is_active ?? true);

        return [
            'id' => $this->id,
            'company_fiscal_name' => $this->company_fiscal_name,
            'company_short_name' => $this->company_short_name,
            'rif' => $this->rif,
            'office_phone' => $this->office_phone,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'is_active' => $isActive,
            'status' => $isActive ? 'active' : 'inactive',
            'status_label' => $isActive ? 'Activo' : 'Inactivo',
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ] : null;
            }),
            'registered_date' => $this->created_at?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
