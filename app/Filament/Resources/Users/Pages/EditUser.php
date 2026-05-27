<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Client;
use App\Support\PanelTabs;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $tenantId = UserResource::resolveCurrentTenantId();

        abort_if($tenantId < 1, 403, 'Empresa do usuario nao encontrada.');

        if (! UserResource::canAssignAdminRole()) {
            $data['role'] = 'user';
        }

        $data['client_id'] = $this->normalizeClientId($data);
        $data['tenant_id'] = $tenantId;
        $data['company_id'] = $tenantId;
        $data['allowed_tabs'] = $this->normalizeAllowedTabs($data);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string>|null
     */
    private function normalizeAllowedTabs(array $data): ?array
    {
        if (($data['role'] ?? 'user') === 'admin') {
            return null;
        }

        return PanelTabs::normalize($data['allowed_tabs'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function normalizeClientId(array $data): ?int
    {
        if (($data['role'] ?? 'user') === 'admin') {
            return null;
        }

        $clientId = (int) ($data['client_id'] ?? 0);

        if ($clientId < 1) {
            return null;
        }

        $tenantId = UserResource::resolveCurrentTenantId();

        return Client::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($clientId)
            ->exists()
                ? $clientId
                : null;
    }
}
