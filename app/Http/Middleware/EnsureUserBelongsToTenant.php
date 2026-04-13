<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('currentTenant');
        $user = $request->user();

        if (! $tenant || ! $user || $user->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Unauthorized tenant context'], 403);
        }

        return $next($request);
    }
}
