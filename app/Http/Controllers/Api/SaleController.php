<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class SaleController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/sales",
     *   tags={"Sales"},
     *   summary="List sales",
     *   description="List sales with date and status filters.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="date", in="query", @OA\Schema(type="string", format="date")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $sales = Sale::with(['user', 'items'])
            ->when($request->date, fn ($q, $d) => $q->whereDate('sold_at', $d))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('sold_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($sales);
    }

    /**
     * @OA\Post(
     *   path="/api/sales",
     *   tags={"Sales"},
     *   summary="Create sale/invoice",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(example={"items":{{"product_id":1,"quantity":2}},"payment_method":"cash","amount_paid":500})),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $tenant = $request->attributes->get('currentTenant');
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('tenant_id', $tenant->id)],
            'items.*.quantity' => 'required|numeric|min:0.001',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'required|in:cash,card,bank_transfer,credit',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('tenant_id', $tenant->id)],
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0.0;
            $itemsData = [];

            foreach ($request->items as $item) {
                /** @var Product $product */
                $product = Product::findOrFail($item['product_id']);

                $qty = (float) $item['quantity'];

                if ($product->stock_quantity < $qty) {
                    abort(422, "Insufficient stock for: {$product->name}");
                }

                $lineTotal = $qty * (float) $product->price_per_unit;
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'lineTotal' => $lineTotal,
                ];
            }

            $discountAmt = $request->discount_amount ?? ($subtotal * (($request->discount_percent ?? 0) / 100));
            $total = $subtotal - $discountAmt;
            $change = (float) $request->amount_paid - $total;

            if ($change < 0) {
                abort(422, 'Amount paid is less than total');
            }

            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $request->customer_id,
                'user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmt,
                'discount_percent' => $request->discount_percent ?? 0,
                'tax_amount' => 0,
                'total_amount' => $total,
                'amount_paid' => (float) $request->amount_paid,
                'change_amount' => $change,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'completed',
            ]);

            foreach ($itemsData as $item) {
                $product = $item['product'];
                $qty = $item['quantity'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_type' => $product->unit_type,
                    'unit_label' => $product->unit_label,
                    'quantity' => $qty,
                    'price_per_unit' => (float) $product->price_per_unit,
                    'total_price' => (float) $item['lineTotal'],
                ]);

                $product->deductStock($qty, $sale->id, $request->user()->id, 'sale', 'POS sale');
            }

            return response()->json($sale->load('items'), 201);
        });
    }

    /**
     * @OA\Get(
     *   path="/api/sales/{id}",
     *   tags={"Sales"},
     *   summary="Get sale",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Sale $sale)
    {
        return response()->json($sale->load(['items.product', 'user']));
    }

    public function cancel(Request $request, Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return response()->json(['message' => 'Cannot cancel'], 422);
        }

        DB::transaction(function () use ($request, $sale) {
            $sale->load('items.product');

            foreach ($sale->items as $item) {
                $item->product->addStock(
                    (float) $item->quantity,
                    'return',
                    'Cancelled: ' . $sale->invoice_number,
                    $request->user()->id,
                    $sale->id
                );
            }

            $sale->update(['status' => 'cancelled']);
        });

        return response()->json(['message' => 'Cancelled and stock restored']);
    }

    /**
     * @OA\Get(
     *   path="/api/sales/daily-report",
     *   tags={"Sales"},
     *   summary="Daily sales report",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function dailyReport()
    {
        return response()->json([
            'date' => now()->toDateString(),
            'sales_count' => Sale::whereDate('sold_at', now())->where('status', 'completed')->count(),
            'revenue' => Sale::whereDate('sold_at', now())->where('status', 'completed')->sum('total_amount'),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/sales/monthly-report",
     *   tags={"Sales"},
     *   summary="Monthly sales report",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function monthlyReport()
    {
        $month = now()->format('Y-m');
        return response()->json([
            'month' => $month,
            'sales_count' => Sale::where('status', 'completed')->whereRaw("DATE_FORMAT(sold_at, '%Y-%m') = ?", [$month])->count(),
            'revenue' => Sale::where('status', 'completed')->whereRaw("DATE_FORMAT(sold_at, '%Y-%m') = ?", [$month])->sum('total_amount'),
        ]);
    }
}

