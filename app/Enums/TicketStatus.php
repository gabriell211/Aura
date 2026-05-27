<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case TRIAGE = 'triage';
    case DISPATCHED = 'dispatched';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
}
