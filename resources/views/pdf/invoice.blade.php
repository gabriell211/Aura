<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fatura {{ $invoice->billing_reference }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 24px; }
        .header { width: 100%; margin-bottom: 16px; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .logo { max-height: 56px; max-width: 180px; }
        .company-name { font-size: 17px; font-weight: 700; margin-bottom: 2px; }
        .company-meta { color: #374151; line-height: 1.45; }
        .title { margin: 12px 0 10px; font-size: 16px; font-weight: 700; text-transform: uppercase; }
        .subtitle { color: #4b5563; margin-bottom: 14px; }
        .card { border: 1px solid #d1d5db; margin-bottom: 10px; }
        .card-title { background: #f3f4f6; font-weight: 700; padding: 8px 10px; border-bottom: 1px solid #d1d5db; text-transform: uppercase; }
        .card-body { padding: 8px 10px; }
        .grid-table, .items-table, .boleto-table, .totals-table { width: 100%; border-collapse: collapse; }
        .grid-table td, .boleto-table td { width: 50%; padding: 4px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .field-label { display: block; color: #6b7280; font-size: 10px; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 11px; font-weight: 600; color: #111827; }
        .items-table th, .items-table td { border: 1px solid #e5e7eb; padding: 6px; }
        .items-table th { background: #f9fafb; text-transform: uppercase; font-size: 10px; text-align: left; }
        .text-right { text-align: right; }
        .totals-table td { border: 1px solid #e5e7eb; padding: 6px 8px; }
        .totals-label { width: 70%; text-align: right; background: #f9fafb; font-weight: 700; }
        .totals-value { width: 30%; text-align: right; font-weight: 700; }
        .muted { color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 32%;">
                    @if ($logoDataUri)
                        <img src="{{ $logoDataUri }}" class="logo" alt="Logo">
                    @else
                        <div class="company-name">{{ $company?->trade_name ?: $company?->legal_name ?: 'Empresa' }}</div>
                    @endif
                </td>
                <td style="width: 68%;">
                    <div class="company-name">{{ $company?->trade_name ?: $company?->legal_name ?: 'Empresa' }}</div>
                    <div class="company-meta">
                        Documento: {{ $company?->document ?: '-' }}<br>
                        E-mail: {{ $company?->email ?: '-' }}<br>
                        Telefone: {{ $company?->phone ?: '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">Fatura de Servicos</div>
    <div class="subtitle">
        Referencia {{ $invoice->billing_reference }} | Contrato {{ $invoice->contract?->code ?: '-' }} | Status {{ strtoupper((string) $invoice->status) }}
    </div>

    <div class="card">
        <div class="card-title">Dados da Fatura</div>
        <div class="card-body">
            <table class="grid-table">
                <tr>
                    <td>
                        <span class="field-label">Cliente</span>
                        <span class="value">{{ $invoice->client?->name ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Documento do cliente</span>
                        <span class="value">{{ $invoice->client?->document ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Periodo de apuracao</span>
                        <span class="value">{{ $invoice->period_start?->format('d/m/Y') ?: '-' }} ate {{ $invoice->period_end?->format('d/m/Y') ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Vencimento</span>
                        <span class="value">{{ $invoice->due_date?->format('d/m/Y') ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Unidade</span>
                        <span class="value">{{ $invoice->contract?->clientUnit?->name ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Documento interno</span>
                        <span class="value">{{ $numeroDocumento }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Itens da Fatura</div>
        <div class="card-body">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 13%;">Tipo</th>
                        <th style="width: 45%;">Descricao</th>
                        <th style="width: 14%;" class="text-right">Qtd</th>
                        <th style="width: 14%;" class="text-right">Vlr unit.</th>
                        <th style="width: 14%;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoice->items as $item)
                        <tr>
                            <td>{{ $item->item_type }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-right">{{ number_format((float) $item->quantity, 4, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format((float) $item->line_total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Sem itens registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <table class="totals-table" style="margin-top: 8px;">
                <tr>
                    <td class="totals-label">Subtotal</td>
                    <td class="totals-value">R$ {{ number_format((float) $invoice->subtotal, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="totals-label">Excedente</td>
                    <td class="totals-value">R$ {{ number_format((float) $invoice->excess_total, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="totals-label">Total</td>
                    <td class="totals-value">R$ {{ number_format((float) $invoice->total, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Bloco de Pagamento (Modelo de Boleto)</div>
        <div class="card-body">
            <table class="boleto-table">
                <tr>
                    <td>
                        <span class="field-label">Banco de faturamento</span>
                        <span class="value">{{ $bankLabel }}</span>
                    </td>
                    <td>
                        <span class="field-label">Nosso numero</span>
                        <span class="value">{{ $nossoNumero }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Beneficiario</span>
                        <span class="value">{{ $company?->legal_name ?: '-' }} ({{ $company?->document ?: '-' }})</span>
                    </td>
                    <td>
                        <span class="field-label">Pagador</span>
                        <span class="value">{{ $invoice->client?->name ?: '-' }} ({{ $invoice->client?->document ?: '-' }})</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Vencimento</span>
                        <span class="value">{{ $invoice->due_date?->format('d/m/Y') ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Valor documento</span>
                        <span class="value">R$ {{ number_format((float) $invoice->total, 2, ',', '.') }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="field-label">Linha digitavel</span>
                        <span class="value">{{ $linhaDigitavel }}</span>
                    </td>
                </tr>
            </table>
            <div class="muted" style="margin-top: 8px;">
                Este bloco e um modelo visual de fatura/boleto. Para boleto bancario registrado, complete os dados de convenio/carteira e a linha digitavel real no modulo financeiro.
            </div>
        </div>
    </div>
</body>
</html>
