<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        $tenantId = (int) app('tenant_id');
        $payload = $request->validated();

        $user = User::query()
            ->where('tenant_id', $tenantId)
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $payload['email'])])
            ->first();

        if ($user === null || ! Hash::check((string) $payload['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'As credenciais informadas são inválidas.',
            ]);
        }

        $tokenName = (string) ($payload['device_name'] ?? 'api-token');
        $token = $user->createToken($tokenName);

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'user' => [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'tenant_id' => (int) $user->tenant_id,
                'role' => (string) $user->role,
            ],
        ], 201);
    }

    public function me(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        return $this->success([
            'user' => [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'tenant_id' => (int) $user->tenant_id,
                'role' => (string) $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        $accessToken = $user?->currentAccessToken();

        if ($accessToken !== null) {
            $accessToken->delete();
        } else {
            $user?->tokens()->delete();
        }

        return $this->success([
            'message' => 'Token revogado com sucesso.',
        ]);
    }
}
