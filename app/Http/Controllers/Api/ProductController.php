<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/products",
     *   tags={"Products"},
     *   summary="List products",
     *   description="List tenant products with search/filter/pagination support.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="paginate", in="query", @OA\Schema(type="boolean")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $q = Product::with('category')
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")
                ->orWhere('sku', 'like', "%$s%"))
            ->when($request->unit_type, fn ($q, $u) => $q->where('unit_type', $u))
            ->when($request->featured, fn ($q) => $q->where('is_featured', true))
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name');

        $data = $request->paginate ? $q->paginate($request->per_page ?? 20) : $q->get();

        return response()->json($data);
    }

    /**
     * @OA\Post(
     *   path="/api/products",
     *   tags={"Products"},
     *   summary="Create product",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"category_id":1,"name":"Premium Beads","sku":"PRD-001","unit_type":"piece","unit_label":"piece","price_per_unit":100,"stock_quantity":20,"stock_unit":"piece"})),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $tenant = $request->attributes->get('currentTenant');
        $validated = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('tenant_id', $tenant->id)],
            'name' => 'required|string|max:200',
            'sku' => ['nullable', 'max:100', Rule::unique('products', 'sku')->where('tenant_id', $tenant->id)],
            'description' => 'nullable|string',
            'unit_type' => 'required|in:weight,length,piece,dozen',
            'unit_label' => 'required|string|max:30',
            'price_per_unit' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'low_stock_alert' => 'nullable|numeric|min:0',
            'stock_unit' => 'required|string|max:30',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|max:3072',
            'images' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        return response()->json($product->load('category'), 201);
    }

    /**
     * @OA\Get(
     *   path="/api/products/{id}",
     *   tags={"Products"},
     *   summary="Get product",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Product $product)
    {
        return response()->json($product->load(['category', 'stockMovements' => fn ($q) => $q->latest()->take(20)]));
    }

    /**
     * @OA\Put(
     *   path="/api/products/{id}",
     *   tags={"Products"},
     *   summary="Update product",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"name":"Updated Product","price_per_unit":120})),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(Request $request, Product $product)
    {
        $tenant = $request->attributes->get('currentTenant');
        $validated = $request->validate([
            'category_id' => ['sometimes', Rule::exists('categories', 'id')->where('tenant_id', $tenant->id)],
            'name' => 'sometimes|string|max:200',
            'sku' => ['nullable', 'max:100', Rule::unique('products', 'sku')->where('tenant_id', $tenant->id)->ignore($product->id)],
            'description' => 'nullable|string',
            'unit_type' => 'sometimes|in:weight,length,piece,dozen',
            'unit_label' => 'sometimes|string|max:30',
            'price_per_unit' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'sometimes|numeric|min:0',
            'low_stock_alert' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|max:3072',
            'images' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json($product->load('category'));
    }

    /**
     * @OA\Delete(
     *   path="/api/products/{id}",
     *   tags={"Products"},
     *   summary="Delete (deactivate) product",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);
        $product->delete();
        return response()->json(['message' => 'Product archived']);
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|numeric',
            'type' => 'required|in:purchase,adjustment,return',
            'notes' => 'nullable|string',
        ]);

        $qty = (float) $request->quantity;

        if ($qty === 0.0) {
            return response()->json(['stock_quantity' => $product->stock_quantity]);
        }

        if ($qty > 0) {
            $product->addStock($qty, $request->type, $request->notes ?? '', $request->user()->id, null);
        } else {
            $abs = abs($qty);
            if ($product->stock_quantity < $abs) {
                return response()->json(['message' => "Insufficient stock for {$product->name}"], 422);
            }
            $product->deductStock(
                $abs,
                null,
                $request->user()->id,
                $request->type,
                $request->notes ?? ''
            );
        }

        return response()->json(['stock_quantity' => $product->fresh()->stock_quantity]);
    }

    /**
     * @OA\Get(
     *   path="/api/products/low-stock",
     *   tags={"Products"},
     *   summary="Low stock products",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function lowStock()
    {
        return response()->json(
            Product::where('is_active', true)
                ->whereRaw('stock_quantity <= low_stock_alert')
                ->orderBy('stock_quantity')
                ->get()
        );
    }
}

