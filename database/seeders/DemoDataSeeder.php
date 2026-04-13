<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['id' => 'demo-tenant'],
            [
                'name' => 'Demo Owner',
                'business_name' => 'Demo Store',
                'email' => 'demo@masterpos.com',
                'password' => bcrypt('demo1234'),
                'plan' => 'enterprise',
                'status' => 'active',
                'trial_ends_at' => now()->addYears(5),
            ]
        );

        app()->instance('currentTenant', $tenant);

        $admin = User::updateOrCreate(
            ['email' => 'demo@masterpos.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Owner',
                'password' => 'demo1234',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Extra users for realistic user management screen.
        for ($i = 1; $i <= 12; $i++) {
            User::updateOrCreate(
                ['email' => "staff{$i}@masterpos.com"],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Staff ' . $i,
                    'password' => 'demo1234',
                    'role' => $i % 4 === 0 ? 'admin' : 'cashier',
                    'is_active' => true,
                ]
            );
        }

        $categoryNames = [
            'Grocery Essentials', 'Beverages', 'Snacks', 'Rice & Flour', 'Pulses',
            'Spices', 'Frozen Food', 'Dairy', 'Personal Care', 'Home Cleaning',
            'Stationery', 'Kitchenware', 'Electronics', 'Mobile Accessories', 'Kids Items',
            'Bakery', 'Tea & Coffee', 'Dry Fruits', 'Sweets', 'General Store',
        ];

        $categories = collect();
        foreach ($categoryNames as $idx => $name) {
            $categories->push(
                Category::updateOrCreate(
                    ['name' => $name],
                    [
                        'tenant_id' => $tenant->id,
                        'slug' => Str::slug($name),
                        'description' => $name . ' items for demo sales and inventory',
                        'color' => fake()->hexColor(),
                        'is_active' => true,
                        'display_order' => $idx + 1,
                    ]
                )
            );
        }

        $unitTypes = [
            ['unit_type' => 'piece', 'unit_label' => 'piece', 'stock_unit' => 'piece'],
            ['unit_type' => 'weight', 'unit_label' => 'kg', 'stock_unit' => 'kg'],
            ['unit_type' => 'length', 'unit_label' => 'meter', 'stock_unit' => 'meter'],
            ['unit_type' => 'dozen', 'unit_label' => 'dozen', 'stock_unit' => 'dozen'],
        ];

        // 220 products to make product/catalog screens look full.
        for ($i = 1; $i <= 220; $i++) {
            $u = $unitTypes[array_rand($unitTypes)];
            $category = $categories->random();

            Product::updateOrCreate(
                ['sku' => 'MP-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'tenant_id' => $tenant->id,
                    'category_id' => $category->id,
                    'name' => fake()->randomElement(['Acha', 'Super', 'Premium', 'Family', 'Fresh']) . ' ' .
                        fake()->words(2, true) . ' ' . $i,
                    'description' => fake()->randomElement([
                        'Karachi market mein high demand item',
                        'Rozana use ka product, achi margin',
                        'Wholesale aur retail dono ke liye',
                        'Fast moving item for POS demo',
                    ]),
                    'unit_type' => $u['unit_type'],
                    'unit_label' => $u['unit_label'],
                    'stock_unit' => $u['stock_unit'],
                    'price_per_unit' => fake()->numberBetween(80, 4500),
                    'cost_price' => fake()->numberBetween(50, 3500),
                    'stock_quantity' => fake()->numberBetween(80, 1000),
                    'low_stock_alert' => fake()->numberBetween(10, 60),
                    'is_featured' => $i % 9 === 0,
                    'is_active' => true,
                    'display_order' => $i,
                ]
            );
        }

        // 200 customers for customer screens/history.
        for ($i = 1; $i <= 200; $i++) {
            Customer::updateOrCreate(
                ['email' => "customer{$i}@demo.pk"],
                [
                    'tenant_id' => $tenant->id,
                    'name' => fake()->name(),
                    'phone' => '03' . fake()->numerify('#########'),
                    'address' => fake()->randomElement(['Karachi', 'Lahore', 'Islamabad', 'Faisalabad']) . ', ' . fake()->streetAddress(),
                    'is_active' => true,
                ]
            );
        }

        $users = User::query()->where('is_active', true)->get();
        $products = Product::query()->where('is_active', true)->get();
        $customers = Customer::query()->where('is_active', true)->get();

        if ($products->isEmpty() || $users->isEmpty()) {
            return;
        }

        // 320 sales with multiple line-items to populate dashboard/reports/history screens.
        for ($s = 1; $s <= 320; $s++) {
            $user = $users->random();
            $customer = $customers->random();
            $itemCount = random_int(2, 6);
            $picked = $products->random($itemCount);

            $subtotal = 0.0;
            $rows = [];
            foreach ($picked as $p) {
                $qty = match ($p->unit_type) {
                    'weight', 'length' => (float) random_int(1, 10),
                    'dozen' => (float) random_int(1, 4),
                    default => (float) random_int(1, 8),
                };
                $line = $qty * (float) $p->price_per_unit;
                $subtotal += $line;
                $rows[] = ['product' => $p, 'qty' => $qty, 'line' => $line];
            }

            $discount = round($subtotal * (random_int(0, 12) / 100), 2);
            $total = max(0, round($subtotal - $discount, 2));
            $paid = $total + random_int(0, 500);

            $sale = Sale::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'invoice_number' => Sale::generateInvoiceNumber(),
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'discount_percent' => 0,
                'tax_amount' => 0,
                'total_amount' => $total,
                'amount_paid' => $paid,
                'change_amount' => $paid - $total,
                'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer', 'credit']),
                'status' => fake()->randomElement(['completed', 'completed', 'completed', 'cancelled']),
                'notes' => fake()->randomElement(['Walk-in customer', 'WhatsApp order', 'Counter sale', 'Bulk purchase']),
                'sold_at' => now()->subDays(random_int(0, 90))->subMinutes(random_int(1, 1400)),
            ]);

            foreach ($rows as $r) {
                $product = $r['product'];
                $qty = $r['qty'];

                $sale->items()->create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_type' => $product->unit_type,
                    'unit_label' => $product->unit_label,
                    'quantity' => $qty,
                    'price_per_unit' => (float) $product->price_per_unit,
                    'total_price' => $r['line'],
                ]);

                if ($sale->status === 'completed') {
                    $product->deductStock($qty, $sale->id, $user->id, 'sale', 'Demo seeded sale');
                }
            }
        }

        // Keep admin user fresh/active for login demos.
        $admin->update(['is_active' => true, 'role' => 'admin']);
    }
}

