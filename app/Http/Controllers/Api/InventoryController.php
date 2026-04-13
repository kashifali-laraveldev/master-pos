<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class InventoryController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/inventory",
     *   tags={"Inventory"},
     *   summary="Inventory list",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index()
    {
        return response()->json(Product::orderBy('name')->paginate(30));
    }

    /**
     * @OA\Post(
     *   path="/api/inventory/adjust",
     *   tags={"Inventory"},
     *   summary="Adjust stock",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"product_id":1,"quantity":5,"type":"adjustment","notes":"Manual correction"})),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
            'type' => 'required|in:purchase,adjustment,return',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $qty = (float) $data['quantity'];

        if ($qty >= 0) {
            $product->addStock($qty, $data['type'], $data['notes'] ?? '', $request->user()->id);
        } else {
            $product->deductStock(abs($qty), null, $request->user()->id, $data['type'], $data['notes'] ?? '');
        }

        return response()->json(['stock_quantity' => $product->fresh()->stock_quantity]);
    }

    /**
     * @OA\Get(
     *   path="/api/inventory/history",
     *   tags={"Inventory"},
     *   summary="Inventory movement history",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function history()
    {
        return response()->json(StockMovement::with(['product', 'user'])->latest()->paginate(50));
    }
}
