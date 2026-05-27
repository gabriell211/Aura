<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Validation\ValidationException;

class TicketAutomationService
{
    public function openForAlert(array $attributes): Ticket
    {
        if (! isset($attributes['client_id']) || (int) $attributes['client_id'] < 1) {
            throw ValidationException::withMessages([
                'client_id' => 'A valid client_id is required to open a ticket.',
            ]);
        }

        $tenantId = (int) $attributes['tenant_id'];
        $externalSource = filled($attributes['external_source'] ?? null) ? trim((string) $attributes['external_source']) : null;
        $externalReference = filled($attributes['external_reference'] ?? null) ? trim((string) $attributes['external_reference']) : null;

        $status = (string) ($attributes['status'] ?? TicketStatus::OPEN->value);

        if (! in_array($status, array_column(TicketStatus::cases(), 'value'), true)) {
            $status = TicketStatus::OPEN->value;
        }

        $payload = [
            'tenant_id' => $tenantId,
            'client_id' => (int) $attributes['client_id'],
            'equipment_id' => $attributes['equipment_id'] ?? null,
            'title' => (string) $attributes['title'],
            'description' => $attributes['description'] ?? null,
            'priority' => $attributes['priority'] ?? 'medium',
            'status' => $status,
            'origin' => $attributes['origin'] ?? 'monitoring',
            'opened_at' => $attributes['opened_at'] ?? now(),
            'closed_at' => $attributes['closed_at'] ?? null,
            'external_source' => $externalSource,
            'external_reference' => $externalReference,
            'external_payload_hash' => $attributes['external_payload_hash'] ?? null,
            'external_last_synced_at' => $externalSource !== null ? now() : null,
        ];

        if (in_array($status, [TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value], true) && blank($payload['closed_at'])) {
            $payload['closed_at'] = now();
        }

        if ($status === TicketStatus::OPEN->value) {
            $payload['closed_at'] = null;
        }

        if ($externalSource !== null && $externalReference !== null) {
            $existingTicket = Ticket::query()
                ->withTrashed()
                ->where('tenant_id', $tenantId)
                ->where('external_source', $externalSource)
                ->where('external_reference', $externalReference)
                ->first();

            if ($existingTicket !== null) {
                if ($existingTicket->trashed()) {
                    $existingTicket->restore();
                }

                $existingTicket->fill($payload);
                $existingTicket->save();

                return $existingTicket->refresh();
            }
        }

        return Ticket::query()->create($payload);
    }
}
