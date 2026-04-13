<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['name' => 'Moti', 'color' => '#8b5cf6', 'description' => 'Pearls and beads'],
            ['name' => 'Taaro', 'color' => '#f59e0b', 'description' => 'Gold/silver threads and wire'],
            ['name' => 'Dhaagey', 'color' => '#10b981', 'description' => 'Thread bundles and spools'],
            ['name' => 'Guchhiyaan', 'color' => '#ef4444', 'description' => 'Thread clusters and tassels'],
            ['name' => 'Lace', 'color' => '#3b82f6', 'description' => 'Decorative laces'],
            ['name' => 'Buttons', 'color' => '#f97316', 'description' => 'Buttons and fasteners'],
        ];

        foreach ($cats as $i => $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']],
                array_merge($cat, [
                    'slug' => Str::slug($cat['name']),
                    'is_active' => true,
                    'display_order' => $i + 1,
                ])
            );
        }
    }
}

