<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;
        $tenantId = $request->header('X-Tenant-Id')
            ?: $request->header('X-Tenant')
            ?: $request->input('tenant_id')
            ?: $request->query('tenant_id');

        if ($tenantId) {
            $tenant = Tenant::query()->find($tenantId);
        }

        if (! $tenant) {
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0] ?? null;
            if ($subdomain && ! in_array($subdomain, ['www', 'localhost', '127'], true)) {
                $tenant = Tenant::query()->where('id', $subdomain)->first();
            }
        }

        // For authenticated routes, fallback to the user's tenant when
        // tenant header/subdomain is not provided (common in Swagger usage).
        if (! $tenant && $request->user()?->tenant_id) {
            $tenant = Tenant::query()->find($request->user()->tenant_id);
        }

        if (! $tenant || $tenant->status !== 'active') {
            return response()->json(['message' => 'Tenant not found or inactive'], 403);
        }

        app()->instance('currentTenant', $tenant);
        $request->attributes->set('currentTenant', $tenant);

        return $next($request);
    }
}
