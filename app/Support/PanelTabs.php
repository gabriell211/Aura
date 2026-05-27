<?php

namespace App\Support;

class PanelTabs
{
    public const DASHBOARD = 'dashboard';
    public const CLIENTS = 'clients';
    public const CONTRACTS = 'contracts';
    public const EQUIPMENT = 'equipment';
    public const CLIENT_EQUIPMENT = 'client_equipment';
    public const METER_READS = 'meter_reads';
    public const TICKETS = 'tickets';
    public const ROUTES = 'routes';
    public const TECHNICIAN_ORDERS = 'technician_orders';
    public const TECHNICIANS = 'technicians';
    public const INVOICES = 'invoices';
    public const STOCK_ITEMS = 'stock_items';
    public const SETTINGS = 'settings';
    public const USERS = 'users';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::DASHBOARD => 'Dashboard',
            self::CLIENTS => 'Clientes',
            self::CONTRACTS => 'Contratos',
            self::EQUIPMENT => 'Equipamentos',
            self::CLIENT_EQUIPMENT => 'Equipamentos Cliente',
            self::METER_READS => 'Contadores',
            self::TICKETS => 'Chamados',
            self::ROUTES => 'Rotas',
            self::TECHNICIAN_ORDERS => 'Minhas OS',
            self::TECHNICIANS => 'Tecnicos',
            self::INVOICES => 'Faturas',
            self::STOCK_ITEMS => 'Suprimentos',
            self::SETTINGS => 'Configuracoes',
            self::USERS => 'Usuarios',
        ];
    }

    /**
     * @param  array<int, string>|null  $tabs
     * @return array<int, string>
     */
    public static function normalize(?array $tabs): array
    {
        if (! is_array($tabs)) {
            return [];
        }

        $valid = array_keys(self::options());

        return array_values(array_unique(array_values(array_intersect($tabs, $valid))));
    }

    /**
     * @param  array<int, string>|null  $tabs
     * @return array<int, string>
     */
    public static function labelsFor(?array $tabs): array
    {
        $labels = [];
        $options = self::options();

        foreach (self::normalize($tabs) as $tab) {
            $labels[] = $options[$tab] ?? $tab;
        }

        return $labels;
    }
}
