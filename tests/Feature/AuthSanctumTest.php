<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthSanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_me_and_logout_flow(): void
    {
        $company = $this->makeCompany('Tenant A');
        $user = $this->makeUser($company, 'admin@tenant-a.local', 'secret123');

        $loginResponse = $this->withHeaders([
            'X-Tenant-Id' => (string) $company->id,
        ])->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
            'device_name' => 'phpunit',
        ]);

        $loginResponse
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.user.email', $user->email);

        $token = $loginResponse->json('data.access_token');
        $tokenId = (int) explode('|', (string) $token)[0];

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Id' => (string) $company->id,
        ])->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', $user->email);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Id' => (string) $company->id,
        ])->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('data.message', 'Token revogado com sucesso.');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_login_rejects_user_from_another_tenant(): void
    {
        $tenantA = $this->makeCompany('Tenant A');
        $tenantB = $this->makeCompany('Tenant B');

        $user = $this->makeUser($tenantA, 'admin@tenant-a.local', 'secret123');

        $this->withHeaders([
            'X-Tenant-Id' => (string) $tenantB->id,
        ])->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'As credenciais informadas são inválidas.');
    }

    private function makeCompany(string $name): Company
    {
        return Company::query()->create([
            'uuid' => (string) Str::uuid(),
            'legal_name' => $name.' LTDA',
            'trade_name' => $name,
            'plan' => 'start',
            'is_active' => true,
        ]);
    }

    private function makeUser(Company $company, string $email, string $password): User
    {
        return User::query()->create([
            'name' => 'Admin '.$company->trade_name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'company_id' => $company->id,
            'tenant_id' => $company->id,
        ]);
    }
}
