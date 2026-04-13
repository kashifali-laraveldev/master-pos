<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->is('api/auth/*') ||
            $request->is('api/health') ||
            $request->is('api/billing/*')
        ) {
            return $next($request);
        }

        $tenant = $request->attributes->get('currentTenant');
        if (! $tenant) {
            return response()->json(['message' => 'Tenant context missing'], 403);
        }

        $isTrialExpired = $tenant->trial_ends_at && now()->greaterThan($tenant->trial_ends_at);
        $hasActivePlan = in_array($tenant->plan, ['pro', 'enterprise'], true);

        if ($isTrialExpired && ! $hasActivePlan) {
            return response()->json([
                'error' => 'subscription_expired',
                'message' => 'Your trial has ended. Please upgrade.',
                'upgrade_url' => '/billing',
            ], 402);
        }

        return $next($request);
    }
}
