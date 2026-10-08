<?php

namespace App\Http\Resources;

use App\Enums\GeneratorStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCheckpointResource extends JsonResource
{
    /**
     * Transforma el checkpoint a un array JSON seguro para consulta pública.
     * Omite intencionalmente datos internos como usuario operador, ID de usuario y notas privadas.
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
            'status' => $statusEnum ? $statusEnum->value : $this->status,
            'status_label' => $statusEnum ? $statusEnum->label() : $this->status,
            'status_color' => $statusEnum ? $statusEnum->color() : 'gray',
            'checkpoint_name' => $this->checkpoint_name,
            'event_date' => $this->event_date?->format('Y-m-d H:i'),
            'event_date_iso' => $this->event_date?->toIso8601String(),
            'notes' => $this->notes,
            'is_verified' => true,
        ];
    }
}
