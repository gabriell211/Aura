<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class InvoicePdfController extends Controller
{
    public function __invoke(Request $request, int $invoice): Response
    {
        $tenantId = $this->resolveTenantId($request);
        app()->instance('tenant_id', $tenantId);

        $invoice = Invoice::query()->with([
            'client',
            'contract.clientUnit',
            'items',
            'payments',
        ])->findOrFail($invoice);

        $company = Company::query()->find($tenantId);
        $bankOptions = (array) config('aura.billing.banks', []);
        $bankCode = (string) ($company?->billing_bank ?: 'outro');
        $bankLabel = (string) ($bankOptions[$bankCode] ?? 'Banco nao informado');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $company,
            'bankLabel' => $bankLabel,
            'logoDataUri' => $this->resolveLogoDataUri($company?->logo_path),
            'nossoNumero' => sprintf('%06d/%s', (int) $invoice->id, (string) $invoice->billing_reference),
            'numeroDocumento' => sprintf('FAT-%s-%06d', (string) $invoice->billing_reference, (int) $invoice->id),
            'linhaDigitavel' => 'CONFIGURAR LINHA DIGITAVEL NO MODULO FINANCEIRO',
        ])->setPaper('a4');

        return $pdf->stream(sprintf('fatura-%s.pdf', $invoice->billing_reference));
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
