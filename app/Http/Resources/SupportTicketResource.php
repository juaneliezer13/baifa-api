<?php

namespace App\Http\Resources;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusEnum = $this->status instanceof TicketStatus
            ? $this->status
            : TicketStatus::tryFrom((string) $this->status);

        $categoryEnum = TicketCategory::tryFrom((string) $this->category);

        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'category' => $this->category,
            'category_label' => $categoryEnum ? $categoryEnum->label() : ucfirst(str_replace('_', ' ', (string) $this->category)),
            'description' => $this->description,
            'status' => $statusEnum ? $statusEnum->value : $this->status,
            'status_label' => $statusEnum ? $statusEnum->label() : $this->status,
            'status_color' => $statusEnum ? $statusEnum->color() : 'gray',
            'final_comment' => $this->final_comment,
            'closed_at' => $this->closed_at?->toIso8601String(),
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'client' => $this->client ? [
                'id' => $this->client->id,
                'company_fiscal_name' => $this->client->company_fiscal_name,
                'rif' => $this->client->rif,
            ] : null,
            'assigned_agent' => $this->assignedAgent ? [
                'id' => $this->assignedAgent->id,
                'name' => $this->assignedAgent->name,
                'email' => $this->assignedAgent->email,
                'role' => $this->assignedAgent->role?->value,
                'role_label' => $this->assignedAgent->role?->label(),
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'messages_count' => $this->whenCounted('messages'),
            'messages' => TicketMessageResource::collection($this->whenLoaded('messages')),
            'logs' => TicketLogResource::collection($this->whenLoaded('logs')),
        ];
    }
}
