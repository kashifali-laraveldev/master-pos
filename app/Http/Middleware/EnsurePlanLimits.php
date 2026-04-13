<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanLimits
{
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $tenant = $request->attributes->get('currentTenant');

        if (! $tenant) {
            return response()->json(['message' => 'Missing tenant context'], 403);
        }

        if ($tenant->plan === 'free') {
            if ($resource === 'products' && Product::count() >= 100) {
                return response()->json(['message' => 'Free plan allows max 100 products'], 422);
            }

            if ($resource === 'users' && User::count() >= 1) {
                return response()->json(['message' => 'Free plan allows max 1 user'], 422);
            }
        }

        return $next($request);
    }
}
