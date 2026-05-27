<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\EquipmentStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Equipment;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = (string) env('SEED_ADMIN_EMAIL', 'admin@aura-mps.local');
        $adminPassword = (string) env('SEED_ADMIN_PASSWORD', 'password');

        $company = Company::query()->create([
            'uuid' => (string) Str::uuid(),
            'legal_name' => 'Aura Print Services LTDA',
            'trade_name' => 'Aura MPS',
            'document' => '00.000.000/0001-00',
            'email' => 'contato@aura-mps.local',
            'phone' => '+55 11 99999-0000',
            'plan' => 'pro',
            'equipment_limit' => 1000,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Admin Aura',
            'email' => $adminEmail,
            'password' => $adminPassword,
            'company_id' => $company->id,
            'tenant_id' => $company->id,
            'role' => 'admin',
        ]);

        $company->update([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        app()->instance('tenant_id', $company->id);

        $client = Client::query()->create([
            'tenant_id' => $company->id,
            'name' => 'Cliente Demo Corp',
            'document' => '11.111.111/0001-11',
            'email' => 'financeiro@demo-corp.local',
            'phone' => '+55 11 98888-7777',
            'billing_contact' => 'Financeiro Demo Corp',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $contract = Contract::query()->create([
            'tenant_id' => $company->id,
            'client_id' => $client->id,
            'code' => 'CTR-DEMO-001',
            'type' => ContractType::FULL_OUTSOURCING->value,
            'start_date' => now()->startOfYear()->toDateString(),
            'monthly_fee' => 1200.00,
            'included_bw_pages' => 15000,
            'included_color_pages' => 2000,
            'bw_overage_price' => 0.08,
            'color_overage_price' => 0.25,
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Equipment::query()->create([
            'tenant_id' => $company->id,
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'manufacturer' => 'HP',
            'model' => 'LaserJet Enterprise MFP M634',
            'serial_number' => 'SN-DEMO-001',
            'ip_address' => '10.10.10.20',
            'location' => 'Recepcao',
            'status' => EquipmentStatus::ONLINE->value,
            'installed_at' => now()->subMonths(3),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
