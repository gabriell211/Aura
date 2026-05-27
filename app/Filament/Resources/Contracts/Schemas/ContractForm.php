<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Enums\ContractType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Contrato')
                    ->schema([
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('client_unit_id')
                            ->label('Unidade')
                            ->relationship('clientUnit', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(100),
                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                ContractType::LEASE->value => 'Locação',
                                ContractType::FRANCHISE->value => 'Franquia',
                                ContractType::COST_PER_PAGE->value => 'Custo por Página',
                                ContractType::FULL_OUTSOURCING->value => 'Outsourcing Completo',
                            ])
                            ->required(),
                        DatePicker::make('start_date')
                            ->label('Início')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Fim'),
                        TextInput::make('monthly_fee')
                            ->label('Mensalidade')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'suspended' => 'Suspenso',
                                'closed' => 'Encerrado',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
                Section::make('Configuração Impressão e Faturamento')
                    ->schema([
                        Select::make('payment_method')
                            ->label('Forma pagamento')
                            ->options([
                                'cobranca_bancaria' => 'Cobrança bancária',
                                'pix' => 'PIX',
                                'debito_automatico' => 'Débito automático',
                                'outros' => 'Outros',
                            ])
                            ->default('cobranca_bancaria')
                            ->required(),
                        Select::make('reading_period')
                            ->label('Período coleta leitura')
                            ->options([
                                'manha' => 'Manhã',
                                'tarde' => 'Tarde',
                                'noite' => 'Noite',
                            ])
                            ->default('tarde')
                            ->required(),
                        TextInput::make('reading_fixed_day')
                            ->label('Dia fixo')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(31)
                            ->default(27),
                        DatePicker::make('reading_start_date')
                            ->label('Data inicial coleta'),
                        DatePicker::make('reading_end_date')
                            ->label('Data final coleta'),
                        TextInput::make('due_days')
                            ->label('Prazo em dias corridos')
                            ->integer()
                            ->minValue(0)
                            ->default(15)
                            ->required(),
                        Select::make('print_type')
                            ->label('Tipo impressão')
                            ->options([
                                'suporte_setor' => 'Suporte/Setor',
                                'departamento' => 'Departamento',
                                'cliente' => 'Cliente',
                            ])
                            ->default('suporte_setor')
                            ->required(),
                        Select::make('counter_display_mode')
                            ->label('Mostrar CI')
                            ->options([
                                'pt_color' => 'PT e COLOR',
                                'pt' => 'Somente PT',
                                'color' => 'Somente Color',
                            ])
                            ->default('pt_color')
                            ->required(),
                        Toggle::make('allow_extension')
                            ->label('Prorrogar')
                            ->inline(false)
                            ->default(false),
                        Toggle::make('show_observation')
                            ->label('Mostrar observação')
                            ->inline(false)
                            ->default(false),
                        Toggle::make('issue_boleto')
                            ->label('Emitir boleto')
                            ->inline(false)
                            ->default(true),
                        Toggle::make('unified_boleto')
                            ->label('Boleto unificado')
                            ->inline(false)
                            ->default(false),
                        Toggle::make('unified_contract')
                            ->label('Contrato unificado')
                            ->inline(false)
                            ->default(false),
                        TextInput::make('external_contract_number')
                            ->label('Nro contrato')
                            ->maxLength(100),
                    ])
                    ->columns(2),
                Section::make('Configuração Faturamento Global')
                    ->schema([
                        TextInput::make('global_bw_franchise_value')
                            ->label('Franquia global PT (valor)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('included_bw_pages')
                            ->label('Franquia global PT (nro cópias)')
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->default(0),
                        TextInput::make('bw_overage_price')
                            ->label('Valor cópia exc. PT')
                            ->numeric()
                            ->required()
                            ->default(0.044),
                        TextInput::make('global_color_franchise_value')
                            ->label('Franquia global color (valor)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('included_color_pages')
                            ->label('Franquia global color (nro cópias)')
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->default(0),
                        TextInput::make('color_overage_price')
                            ->label('Valor cópia exc. color')
                            ->numeric()
                            ->required()
                            ->default(0.670),
                    ])
                    ->columns(2),
            ]);
    }
}
