<?php

namespace App\Enums;

enum ContractType: string
{
    case LEASE = 'lease';
    case FRANCHISE = 'franchise';
    case COST_PER_PAGE = 'cost_per_page';
    case FULL_OUTSOURCING = 'full_outsourcing';
}
