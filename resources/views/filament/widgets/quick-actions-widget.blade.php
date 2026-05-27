@php
    use App\Filament\Pages\CompanySettings;
    use App\Filament\Resources\Clients\ClientResource;
    use App\Filament\Resources\Contracts\ContractResource;
    use App\Filament\Resources\Equipment\EquipmentResource;
    use App\Filament\Resources\Invoices\InvoiceResource;
    use App\Filament\Resources\StockItems\StockItemResource;
    use App\Filament\Resources\Tickets\TicketResource;

    $links = [
        ['label' => 'Clientes', 'description' => 'Cadastro e relacionamento', 'icon' => 'heroicon-o-users', 'url' => ClientResource::getUrl()],
        ['label' => 'Contratos', 'description' => 'Planos e franquias', 'icon' => 'heroicon-o-document-text', 'url' => ContractResource::getUrl()],
        ['label' => 'Equipamentos', 'description' => 'Parque monitorado', 'icon' => 'heroicon-o-printer', 'url' => EquipmentResource::getUrl()],
        ['label' => 'Chamados', 'description' => 'SLA e suporte', 'icon' => 'heroicon-o-wrench-screwdriver', 'url' => TicketResource::getUrl()],
        ['label' => 'Faturas', 'description' => 'Cobranca e historico', 'icon' => 'heroicon-o-banknotes', 'url' => InvoiceResource::getUrl()],
        ['label' => 'Suprimentos', 'description' => 'Estoque e reposicao', 'icon' => 'heroicon-o-archive-box', 'url' => StockItemResource::getUrl()],
        ['label' => 'Configuracoes', 'description' => 'Logo e dados da empresa', 'icon' => 'heroicon-o-cog-6-tooth', 'url' => CompanySettings::getUrl()],
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        heading="Acoes Rapidas"
        description="Atalhos para os modulos mais usados no dia a dia."
    >
        <div class="grid gap-2">
            @foreach ($links as $link)
                <a
                    href="{{ $link['url'] }}"
                    class="fi-link block rounded-lg border border-gray-200/70 p-3 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5"
                >
                    <div class="flex items-center gap-2">
                        <x-filament::icon :icon="$link['icon']" class="h-5 w-5 text-primary-500" />
                        <span class="text-sm font-semibold">{{ $link['label'] }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $link['description'] }}</p>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

