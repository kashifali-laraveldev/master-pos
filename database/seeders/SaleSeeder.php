<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        if (! $cashier) {
            return;
        }

        $products = Product::all();
        if ($products->isEmpty()) {
            return;
        }

        for ($d = 6; $d >= 0; $d--) {
            $numSales = rand(3, 7);

            for ($s = 0; $s < $numSales; $s++) {
                $items = [];
                $subtotal = 0.0;

                $randCount = rand(1, 4);
                $randProducts = $products->random($randCount);

                // Collection::random($count) may return a Collection (for $count > 1).
                if ($randProducts instanceof \Illuminate\Support\Collection) {
                    $randProducts = $randProducts->all();
                } elseif (!is_array($randProducts)) {
                    $randProducts = [$randProducts];
                }

                foreach ($randProducts as $p) {
                    $qty = match ($p->unit_type) {
                        'weight' => round(rand(5, 50) / 10, 1),   // 0.5 to 5.0 tolas
                        'length' => round(rand(10, 100) / 10, 1), // 1.0 to 10.0 gaz
                        'piece' => rand(1, 20),
                        'dozen' => rand(1, 5),
                        default => 1,
                    };

                    $lineTotal = $qty * (float) $p->price_per_unit;
                    $subtotal += $lineTotal;

                    $items[] = [
                        'product' => $p,
                        'quantity' => $qty,
                        'lineTotal' => $lineTotal,
                    ];
                }

                $discount = rand(0, 1) ? round($subtotal * 0.05, 2) : 0.0;
                $total = $subtotal - $discount;
                $paid = $total + rand(0, 200);
                $change = $paid - $total;

                $sale = Sale::create([
                    'invoice_number' => Sale::generateInvoiceNumber(),
                    'user_id' => $cashier->id,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'discount_percent' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $total,
                    'amount_paid' => $paid,
                    'change_amount' => $change,
                    'payment_method' => ['cash', 'card', 'cash', 'cash'][rand(0, 3)],
                    'status' => 'completed',
                    'sold_at' => now()->subDays($d)->subHours(rand(1, 8)),
                ]);

                foreach ($items as $item) {
                    $p = $item['product'];
                    $qty = (float) $item['quantity'];

                    $sale->items()->create([
                        'product_id' => $p->id,
                        'product_name' => $p->name,
                        'unit_type' => $p->unit_type,
                        'unit_label' => $p->unit_label,
                        'quantity' => $qty,
                        'price_per_unit' => (float) $p->price_per_unit,
                        'total_price' => (float) $item['lineTotal'],
                    ]);

                    $p->deductStock($qty, $sale->id, $cashier->id, 'sale', 'Seeded sale');
                }
            }
        }
    }
}

