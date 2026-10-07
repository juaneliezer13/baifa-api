<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name ?? 'Sistema Automático',
            'user_role' => $this->user?->role?->label() ?? 'Sistema',
            'action' => $this->action,
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'comment' => $this->comment,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
