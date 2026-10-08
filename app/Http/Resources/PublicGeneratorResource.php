<?php

namespace App\Http\Resources;

use App\Enums\GeneratorStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicGeneratorResource extends JsonResource
{
    /**
     * Transforma el generador a un array JSON público y limitado (estilo MRW).
     * Oculta datos fiscales de la empresa cliente, datos de contacto del cliente y observaciones internas.
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
            'estimated_arrival_date' => $this->estimated_arrival_date?->format('Y-m-d'),
            'photo_url' => $this->photo_url,
            'checkpoints' => PublicCheckpointResource::collection($this->whenLoaded('checkpoints')),
            'is_public_view' => true,
            'requires_auth_for_details' => true,
        ];
    }
}
