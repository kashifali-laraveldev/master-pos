<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'demo@masterpos.com'], [
            'name' => 'Demo Owner',
            // `password` column uses `hashed` cast in `User` model, so store the plain password.
            'password' => 'demo1234',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}

