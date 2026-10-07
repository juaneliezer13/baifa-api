<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
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
            'sender_name' => $this->user?->name ?? ($this->sender_type === 'system' ? 'Sistema' : 'Usuario'),
            'sender_type' => $this->sender_type,
            'message' => $this->message,
            'created_at' => $this->created_at?->toIso8601String(),
            'timestamp' => $this->created_at?->format('H:i'),
        ];
    }
}
