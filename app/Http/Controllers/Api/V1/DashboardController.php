<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Ticket;

class DashboardController extends ApiController
{
    public function summary()
    {
        $openTickets = Ticket::query()->whereNotIn('status', ['closed', 'resolved'])->count();
        $criticalEquipment = Equipment::query()->whereIn('status', ['offline', 'alert'])->count();

        $currentMonth = now()->format((string) config('aura.billing.reference_format', 'Ym'));
        $currentMonthBilling = (float) Invoice::query()
            ->where('billing_reference', $currentMonth)
            ->sum('total');

        return $this->success([
            'clients' => Client::query()->count(),
            'active_contracts' => Contract::query()->where('status', 'active')->count(),
            'equipment_total' => Equipment::query()->count(),
            'open_tickets' => $openTickets,
            'critical_equipment' => $criticalEquipment,
            'current_month_billing' => $currentMonthBilling,
            'billing_reference' => $currentMonth,
        ]);
    }
}
