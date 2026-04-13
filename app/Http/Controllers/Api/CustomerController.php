<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/customers",
     *   tags={"Customers"},
     *   summary="List customers",
     *   description="Returns customers for current tenant.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index()
    {
        return response()->json(Customer::orderByDesc('id')->paginate(20));
    }

    /**
     * @OA\Post(
     *   path="/api/customers",
     *   tags={"Customers"},
     *   summary="Create customer",
     *   description="Creates a new customer under current tenant.",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(example={"name":"John Doe","email":"john@example.com","phone":"+923001112233","address":"Main Market"})
     *   ),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $tenant = $request->attributes->get('currentTenant');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('customers', 'email')->where('tenant_id', $tenant->id)],
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
        ]);

        $data['tenant_id'] = $tenant->id;
        return response()->json(Customer::create($data), 201);
    }

    /**
     * @OA\Put(
     *   path="/api/customers/{id}",
     *   tags={"Customers"},
     *   summary="Update customer",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"name":"John Updated","phone":"+923009999999"})),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string|max:30',
            'address' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]));
        return response()->json($customer);
    }

    /**
     * @OA\Delete(
     *   path="/api/customers/{id}",
     *   tags={"Customers"},
     *   summary="Delete customer",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted']);
    }

    /**
     * @OA\Get(
     *   path="/api/customers/{id}/purchase-history",
     *   tags={"Customers"},
     *   summary="Customer purchase history",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function purchaseHistory(Customer $customer)
    {
        return response()->json(
            Sale::with('items')
                ->where('customer_id', $customer->id)
                ->latest('sold_at')
                ->paginate(20)
        );
    }
}
