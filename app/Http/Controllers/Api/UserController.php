<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/users",
     *   tags={"Users"},
     *   summary="List users",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(
            User::query()
                ->orderBy('id', 'desc')
                ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at', 'updated_at'])
        );
    }

    /**
     * @OA\Post(
     *   path="/api/users",
     *   tags={"Users"},
     *   summary="Invite/create user in tenant",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"name":"Cashier","email":"cashier@example.com","password":"secret123","role":"cashier"})),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tenant = $request->attributes->get('currentTenant');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->where('tenant_id', $tenant->id)],
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,cashier',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            // `password` field uses `hashed` cast in `User` model.
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($user->makeHidden(['password']), 201);
    }

    /**
     * @OA\Put(
     *   path="/api/users/{id}",
     *   tags={"Users"},
     *   summary="Update user or role",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"role":"admin"})),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(Request $request, User $user)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tenant = $request->attributes->get('currentTenant');
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->where('tenant_id', $tenant->id)->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|in:admin,cashier',
            'is_active' => 'nullable|boolean',
        ]);

        if (array_key_exists('password', $validated) && ! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        foreach (['name', 'email', 'role', 'is_active'] as $key) {
            if (array_key_exists($key, $validated)) {
                $user->{$key} = $validated[$key];
            }
        }

        $user->save();

        return response()->json($user->makeHidden(['password']));
    }

    /**
     * @OA\Delete(
     *   path="/api/users/{id}",
     *   tags={"Users"},
     *   summary="Deactivate user",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Request $request, User $user)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Keep it non-destructive: just deactivate.
        $user->update(['is_active' => false]);

        return response()->json(['message' => 'User deactivated']);
    }
}

