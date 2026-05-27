<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>OS {{ $ticket->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 24px; }
        .header { width: 100%; margin-bottom: 16px; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .logo { max-height: 58px; max-width: 180px; }
        .company-name { font-size: 17px; font-weight: 700; margin-bottom: 2px; }
        .company-meta { color: #374151; line-height: 1.45; }
        .title { margin: 12px 0 10px; font-size: 16px; font-weight: 700; text-transform: uppercase; }
        .subtitle { color: #4b5563; margin-bottom: 14px; }
        .card { border: 1px solid #d1d5db; margin-bottom: 10px; }
        .card-title { background: #f3f4f6; font-weight: 700; padding: 8px 10px; border-bottom: 1px solid #d1d5db; text-transform: uppercase; }
        .card-body { padding: 8px 10px; }
        .grid-table { width: 100%; border-collapse: collapse; }
        .grid-table td { width: 50%; padding: 4px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .field-label { display: block; color: #6b7280; font-size: 10px; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 11px; font-weight: 600; color: #111827; }
        .value-normal { font-weight: 400; }
        .text-box { min-height: 74px; padding: 8px; border: 1px solid #e5e7eb; background: #fafafa; white-space: pre-line; }
        .history-table { width: 100%; border-collapse: collapse; }
        .history-table th, .history-table td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; vertical-align: top; }
        .history-table th { background: #f9fafb; font-size: 10px; text-transform: uppercase; }
        .signatures { margin-top: 18px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 50%; padding-top: 28px; padding-right: 12px; }
        .sign-line { border-top: 1px solid #111827; padding-top: 6px; text-align: center; color: #374151; }
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

    <div class="title">Ordem de Servico - Relatorio de Atendimento</div>
    <div class="subtitle">
        OS #{{ $ticket->id }} | Status: {{ strtoupper((string) $ticket->status) }} | Prioridade: {{ strtoupper((string) $ticket->priority) }}
    </div>

    <div class="card">
        <div class="card-title">Dados da OS</div>
        <div class="card-body">
            <table class="grid-table">
                <tr>
                    <td>
                        <span class="field-label">Titulo</span>
                        <span class="value">{{ $ticket->title }}</span>
                    </td>
                    <td>
                        <span class="field-label">Origem</span>
                        <span class="value">{{ $ticket->origin ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Aberta em</span>
                        <span class="value">{{ $ticket->opened_at?->format('d/m/Y H:i') ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Fechada em</span>
                        <span class="value">{{ $ticket->closed_at?->format('d/m/Y H:i') ?: '-' }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Cliente e Equipamento</div>
        <div class="card-body">
            <table class="grid-table">
                <tr>
                    <td>
                        <span class="field-label">Cliente</span>
                        <span class="value">{{ $ticket->client?->name ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Documento</span>
                        <span class="value">{{ $ticket->client?->document ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Contato</span>
                        <span class="value value-normal">{{ $ticket->client?->phone ?: '-' }} | {{ $ticket->client?->email ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Unidade</span>
                        <span class="value">{{ $ticket->equipment?->clientUnit?->name ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Equipamento</span>
                        <span class="value">
                            {{ $ticket->equipment?->manufacturer ?: '-' }}
                            {{ $ticket->equipment?->model ?: '' }}
                        </span>
                    </td>
                    <td>
                        <span class="field-label">Serial</span>
                        <span class="value">{{ $ticket->equipment?->serial_number ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Localizacao</span>
                        <span class="value">{{ $ticket->equipment?->location ?: '-' }}</span>
                    </td>
                    <td>
                        <span class="field-label">IP</span>
                        <span class="value">{{ $ticket->equipment?->ip_address ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Ultima leitura mono</span>
                        <span class="value">{{ number_format((float) ($latestMeterRead?->mono_total ?? 0), 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="field-label">Ultima leitura color</span>
                        <span class="value">{{ number_format((float) ($latestMeterRead?->color_total ?? 0), 0, ',', '.') }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Servico Executado</div>
        <div class="card-body">
            <div class="text-box">{{ $ticket->description ?: 'Sem descricao informada.' }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Historico de Interacoes</div>
        <div class="card-body">
            <table class="history-table">
                <thead>
                    <tr>
                        <th style="width: 24%;">Data</th>
                        <th style="width: 22%;">Usuario</th>
                        <th style="width: 14%;">Tipo</th>
                        <th style="width: 40%;">Mensagem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ticket->interactions->sortByDesc('created_at') as $interaction)
                        <tr>
                            <td>{{ $interaction->created_at?->format('d/m/Y H:i') ?: '-' }}</td>
                            <td>{{ $interaction->user?->name ?: '-' }}</td>
                            <td>{{ $interaction->interaction_type ?: '-' }}</td>
                            <td>{{ $interaction->message }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Sem interacoes registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <table class="signatures">
        <tr>
            <td><div class="sign-line">Tecnico Responsavel</div></td>
            <td><div class="sign-line">Cliente / Responsavel pelo Aceite</div></td>
        </tr>
    </table>
</body>
</html>
