<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['email' => 'demo@masterpos.com'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Demo Owner',
                'business_name' => 'Demo Store',
                'password' => bcrypt('demo1234'),
                'plan' => 'free',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(30),
            ]
        );

        app()->instance('currentTenant', $tenant);

        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}
