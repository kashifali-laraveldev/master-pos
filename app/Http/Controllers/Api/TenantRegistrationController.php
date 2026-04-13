<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TenantWelcomeMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class TenantRegistrationController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/register",
     *   tags={"Auth"},
     *   summary="Register tenant",
     *   description="Creates tenant account and first admin user.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(example={"name":"Demo Owner","business_name":"Demo Store","email":"demo@masterpos.com","password":"demo1234","plan":"free"})
     *   ),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'business_name' => 'required|string|max:150',
            'email' => 'required|email|unique:tenants,email',
            'password' => 'required|string|min:8',
            'plan' => 'nullable|in:free,pro,enterprise',
        ]);

        $tenant = DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'id' => (string) Str::ulid(),
                'name' => $validated['name'],
                'business_name' => $validated['business_name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'plan' => $validated['plan'] ?? 'free',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'admin',
                'is_active' => true,
            ]);

            return $tenant;
        });

        Mail::to($tenant->email)->queue(new TenantWelcomeMail(
            ownerName: $tenant->name,
            businessName: $tenant->business_name,
            email: $tenant->email,
            loginUrl: rtrim((string) config('app.frontend_url', env('FRONTEND_URL', '')), '/') . '/login',
            apiDocsUrl: rtrim((string) config('app.url'), '/') . '/api/documentation'
        ));

        return response()->json([
            'tenant_id' => $tenant->id,
            'business_name' => $tenant->business_name,
            'plan' => $tenant->plan,
        ], 201);
    }
}
