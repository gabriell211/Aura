<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class TicketPdfController extends Controller
{
    public function __invoke(Request $request, int $ticket): Response
    {
        $tenantId = $this->resolveTenantId($request);
        app()->instance('tenant_id', $tenantId);

        $ticket = Ticket::query()->with([
            'client',
            'equipment.clientUnit',
            'interactions' => fn ($query) => $query->latest(),
            'interactions.user',
        ])->findOrFail($ticket);

        $company = Company::query()->find($tenantId);
        $latestMeterRead = $ticket->equipment?->meterReads()->latest('read_at')->first();

        $pdf = Pdf::loadView('pdf.ticket', [
            'ticket' => $ticket,
            'company' => $company,
            'latestMeterRead' => $latestMeterRead,
            'logoDataUri' => $this->resolveLogoDataUri($company?->logo_path),
        ])->setPaper('a4');

        return $pdf->stream(sprintf('os-%s.pdf', $ticket->id));
    }

    protected function resolveTenantId(Request $request): int
    {
        $tenantId = (int) ($request->user()?->tenant_id ?? 0);

        abort_if($tenantId < 1, 403);

        return $tenantId;
    }

    protected function resolveLogoDataUri(?string $logoPath): ?string
    {
        if (blank($logoPath) || ! Storage::disk('public')->exists((string) $logoPath)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path((string) $logoPath);
        $bytes = @file_get_contents($absolutePath);

        if ($bytes === false) {
            return null;
        }

        $mimeType = @mime_content_type($absolutePath) ?: 'image/png';

        return 'data:'.$mimeType.';base64,'.base64_encode($bytes);
    }
}
