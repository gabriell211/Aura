<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = null;

        if ($request->user() !== null && $request->user()->tenant_id !== null) {
            $tenantId = (int) $request->user()->tenant_id;
        }

        if ($tenantId === null && $request->headers->has('X-Tenant-Id')) {
            $tenantId = (int) $request->header('X-Tenant-Id');
        }

        if ($tenantId === null && $request->is('api/v1/printwayy/*') && $request->has('tenant_id')) {
            $tenantId = (int) $request->input('tenant_id');
        }

        if ($tenantId === null || $tenantId < 1) {
            abort(422, 'Tenant context is required.');
        }

        app()->instance('tenant_id', $tenantId);

        return $next($request);
    }
}
