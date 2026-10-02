<?php

namespace App\Http\Resources;

use App\Enums\GeneratorStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckpointResource extends JsonResource
{
    /**
     * Transforma el checkpoint a un array JSON estandarizado para la línea de tiempo.
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
            'generator_id' => $this->generator_id,
            'status' => $statusEnum ? $statusEnum->value : $this->status,
            'status_label' => $statusEnum ? $statusEnum->label() : $this->status,
            'status_color' => $statusEnum ? $statusEnum->color() : 'gray',
            'checkpoint_name' => $this->checkpoint_name,
            'event_date' => $this->event_date?->format('Y-m-d H:i'),
            'event_date_iso' => $this->event_date?->toIso8601String(),
            'notes' => $this->notes,
            'user_id' => $this->user_id,
            'changed_by' => $this->user?->name ?? 'Sistema / Operador',
            'changed_by_role' => $this->user?->role?->label() ?? 'Operador',
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
