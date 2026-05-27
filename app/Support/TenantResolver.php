<?php

namespace App\Support;

use Illuminate\Http\Request;

class TenantResolver
{
    public function resolveFromRequest(Request $request): ?int
    {
        if ($request->user() !== null && $request->user()->tenant_id !== null) {
            return (int) $request->user()->tenant_id;
        }

        if ($request->headers->has('X-Tenant-Id')) {
            return (int) $request->header('X-Tenant-Id');
        }

        return null;
    }
}
