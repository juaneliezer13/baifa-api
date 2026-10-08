<?php

namespace App\Http\Resources;

use App\Enums\GeneratorStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneratorResource extends JsonResource
{
    /**
     * Transforma el recurso de generador a un array JSON estandarizado.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusEnum = $this->status instanceof GeneratorStatus
            ? $this->status
            : GeneratorStatus::tryFrom($this->status);

        return [
            'id' => $this->id,
            'serial_number' => $this->serial_number,
            'name' => $this->name,
            'model' => $this->model,
            'capacity_kva' => $this->capacity_kva !== null ? (float) $this->capacity_kva : null,
            'status' => $statusEnum ? $statusEnum->value : $this->status,
            'status_label' => $statusEnum ? $statusEnum->label() : $this->status,
            'status_color' => $statusEnum ? $statusEnum->color() : 'gray',
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', function () {
                return $this->client ? [
                    'id' => $this->client->id,
                    'company_fiscal_name' => $this->client->company_fiscal_name,
                    'company_short_name' => $this->client->company_short_name,
                    'rif' => $this->client->rif,
                    'contact_name' => $this->client->contact_name,
                    'contact_phone' => $this->client->contact_phone,
                ] : null;
            }),
            'estimated_arrival_date' => $this->estimated_arrival_date?->format('Y-m-d'),
            'photo_path' => $this->photo_path,
            'photo_url' => $this->photo_url,
            'notes' => $this->notes,
            'checkpoints' => CheckpointResource::collection($this->whenLoaded('checkpoints')),
            'is_public_view' => false,
            'requires_auth_for_details' => false,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
