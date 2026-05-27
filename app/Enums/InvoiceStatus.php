<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELED = 'canceled';
}
