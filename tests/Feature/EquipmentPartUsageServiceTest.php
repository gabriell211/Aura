<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\EquipmentPartUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EquipmentPartUsageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_consumes_stock_when_registering_parts_for_reconditioned_machine(): void
    {
        $company = $this->makeCompany('Santa Print');
        $user = $this->makeUser($company, 'admin@santa.local');
        $client = $this->makeClient($company, 'Cliente Santa');
        $stockItem = $this->makeStockItem($company, 'KIT-FUSOR', 'Kit Fusor', 10);

        $equipment = Equipment::query()->create([
            'tenant_id' => $company->id,
            'client_id' => $client->id,
            'manufacturer' => 'RICOH',
            'model' => 'SP 3710',
            'serial_number' => 'EQ-REC-001',
            'status' => 'online',
            'acquisition_type' => Equipment::ACQUISITION_RECONDITIONED,
        ]);

        app(EquipmentPartUsageService::class)->syncForEquipment(
            equipment: $equipment,
            partUsages: [
                [
                    'stock_item_id' => $stockItem->id,
                    'quantity' => 3,
                    'notes' => 'Troca de kit de fusao.',
                ],
            ],
            acquisitionType: Equipment::ACQUISITION_RECONDITIONED,
        );

        $stockItem->refresh();

        $this->assertSame(7, (int) $stockItem->current_stock);

        $this->assertDatabaseHas('equipment_part_usages', [
            'equipment_id' => $equipment->id,
            'stock_item_id' => $stockItem->id,
            'quantity' => 3,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'movement_type' => StockMovement::TYPE_INSTALLATION,
            'quantity' => -3,
            'reference_type' => Equipment::class,
            'reference_id' => $equipment->id,
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_it_returns_stock_and_clears_parts_when_equipment_changes_to_new(): void
    {
        $company = $this->makeCompany('Santa Print');
        $this->makeUser($company, 'admin@santa.local');
        $client = $this->makeClient($company, 'Cliente Santa');
        $stockItem = $this->makeStockItem($company, 'CIL-001', 'Cilindro', 8);

        $equipment = Equipment::query()->create([
            'tenant_id' => $company->id,
            'client_id' => $client->id,
            'manufacturer' => 'HP',
            'model' => 'M425',
            'serial_number' => 'EQ-REC-002',
            'status' => 'online',
            'acquisition_type' => Equipment::ACQUISITION_RECONDITIONED,
        ]);

        $service = app(EquipmentPartUsageService::class);

        $service->syncForEquipment(
            equipment: $equipment,
            partUsages: [
                [
                    'stock_item_id' => $stockItem->id,
                    'quantity' => 4,
                ],
            ],
            acquisitionType: Equipment::ACQUISITION_RECONDITIONED,
        );

        $service->syncForEquipment(
            equipment: $equipment,
            partUsages: [],
            acquisitionType: Equipment::ACQUISITION_NEW,
        );

        $stockItem->refresh();

        $this->assertSame(8, (int) $stockItem->current_stock);

        $activePartUsages = $equipment->partUsages()->count();

        $this->assertSame(0, $activePartUsages);

        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'movement_type' => StockMovement::TYPE_RETURN,
            'quantity' => 4,
            'reference_type' => Equipment::class,
            'reference_id' => $equipment->id,
        ]);
    }

    private function makeCompany(string $tradeName): Company
    {
        return Company::query()->create([
            'uuid' => (string) Str::uuid(),
            'legal_name' => $tradeName.' LTDA',
            'trade_name' => $tradeName,
            'plan' => 'start',
            'is_active' => true,
        ]);
    }

    private function makeUser(Company $company, string $email): User
    {
        $user = User::query()->create([
            'name' => 'Admin '.$company->trade_name,
            'email' => $email,
            'password' => 'password123',
            'role' => 'admin',
            'company_id' => $company->id,
            'tenant_id' => $company->id,
        ]);

        $this->actingAs($user, 'web');
        app()->instance('tenant_id', $company->id);

        return $user;
    }

    private function makeClient(Company $company, string $name): Client
    {
        return Client::query()->create([
            'tenant_id' => $company->id,
            'name' => $name,
        ]);
    }

    private function makeStockItem(Company $company, string $sku, string $name, int $currentStock): StockItem
    {
        return StockItem::query()->create([
            'tenant_id' => $company->id,
            'sku' => $sku,
            'name' => $name,
            'unit' => 'un',
            'minimum_stock' => 0,
            'current_stock' => $currentStock,
            'lifecycle_stage' => StockItem::LIFECYCLE_STAGE_IN_STOCK,
        ]);
    }
}
