<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@sigap.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('admin');

        // Create Sample Sales User
        $sales = User::create([
            'name' => 'Sales Demo',
            'email' => 'sales@sigap.com',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'role' => 'sales',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $sales->assignRole('sales');

        // Create Sample Gudang User
        $gudang = User::create([
            'name' => 'Gudang Demo',
            'email' => 'gudang@sigap.com',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'role' => 'gudang',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $gudang->assignRole('gudang');

        // Create Sample Supir User
        $supir = User::create([
            'name' => 'Supir Demo',
            'email' => 'supir@sigap.com',
            'password' => Hash::make('password'),
            'phone' => '081234567893',
            'role' => 'supir',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $supir->assignRole('supir');
    }
}
