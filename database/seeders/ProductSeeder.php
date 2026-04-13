<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $moti = Category::where('name', 'Moti')->firstOrFail();
        $taaro = Category::where('name', 'Taaro')->firstOrFail();
        $dhaagey = Category::where('name', 'Dhaagey')->firstOrFail();
        $lace = Category::where('name', 'Lace')->firstOrFail();
        $buttons = Category::where('name', 'Buttons')->firstOrFail();

        $products = [
            [
                'category_id' => $moti->id,
                'name' => 'Safed Moti (White Pearls)',
                'sku' => 'MTI-001',
                'unit_type' => 'weight',
                'unit_label' => 'tola',
                'price_per_unit' => 850.00,
                'cost_price' => 600.00,
                'stock_quantity' => 500.000,
                'low_stock_alert' => 50,
                'stock_unit' => 'tola',
                'is_featured' => true,
                'description' => 'Premium quality white pearls, sold per tola (11.66g)',
            ],
            [
                'category_id' => $moti->id,
                'name' => 'Rang Birangi Moti (Colorful Beads)',
                'sku' => 'MTI-002',
                'unit_type' => 'weight',
                'unit_label' => 'tola',
                'price_per_unit' => 450.00,
                'cost_price' => 300.00,
                'stock_quantity' => 800.000,
                'low_stock_alert' => 80,
                'stock_unit' => 'tola',
                'is_featured' => true,
                'description' => 'Mixed colorful beads for crafts',
            ],
            [
                'category_id' => $taaro->id,
                'name' => 'Sona Taaro (Gold Thread)',
                'sku' => 'TAR-001',
                'unit_type' => 'weight',
                'unit_label' => 'tola',
                'price_per_unit' => 1200.00,
                'cost_price' => 900.00,
                'stock_quantity' => 200.000,
                'low_stock_alert' => 20,
                'stock_unit' => 'tola',
                'is_featured' => true,
                'description' => 'Gold metallic thread for embroidery',
            ],
            [
                'category_id' => $dhaagey->id,
                'name' => 'Reshi Dhaaga (Silk Thread)',
                'sku' => 'DHA-001',
                'unit_type' => 'length',
                'unit_label' => 'gaz',
                'price_per_unit' => 120.00,
                'cost_price' => 80.00,
                'stock_quantity' => 5000.000,
                'low_stock_alert' => 500,
                'stock_unit' => 'gaz',
                'is_featured' => false,
                'description' => 'Fine silk thread, sold per gaz',
            ],
            [
                'category_id' => $lace->id,
                'name' => 'Safed Lace (White Lace)',
                'sku' => 'LAC-001',
                'unit_type' => 'length',
                'unit_label' => 'gaz',
                'price_per_unit' => 85.00,
                'cost_price' => 55.00,
                'stock_quantity' => 3000.000,
                'low_stock_alert' => 300,
                'stock_unit' => 'gaz',
                'is_featured' => false,
                'description' => 'Decorative white lace trim, per gaz',
            ],
            [
                'category_id' => $buttons->id,
                'name' => 'Sitaaray Button (Star Buttons)',
                'sku' => 'BTN-001',
                'unit_type' => 'piece',
                'unit_label' => 'piece',
                'price_per_unit' => 15.00,
                'cost_price' => 8.00,
                'stock_quantity' => 2000.000,
                'low_stock_alert' => 200,
                'stock_unit' => 'piece',
                'is_featured' => false,
                'description' => 'Star shaped decorative buttons',
            ],
            [
                'category_id' => $dhaagey->id,
                'name' => 'Dhaaga Reel (Thread Spool)',
                'sku' => 'DHA-002',
                'unit_type' => 'dozen',
                'unit_label' => 'dozen',
                'price_per_unit' => 360.00,
                'cost_price' => 240.00,
                'stock_quantity' => 150.000,
                'low_stock_alert' => 15,
                'stock_unit' => 'dozen',
                'is_featured' => false,
                'description' => 'Thread spools sold per dozen (12 pcs)',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['sku' => $p['sku']], $p);
        }
    }
}

