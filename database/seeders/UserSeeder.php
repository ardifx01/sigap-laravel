<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Users
        $admins = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.admin@sigap.com',
                'phone' => '081234567890',
            ],
            [
                'name' => 'Sari Wijaya',
                'email' => 'sari.admin@sigap.com',
                'phone' => '081234567891',
            ],
        ];

        foreach ($admins as $admin) {
            $user = User::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => Hash::make('password'),
                'phone' => $admin['phone'],
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(rand(1, 7)),
            ]);
            $user->assignRole('admin');
        }

        // Sales Users
        $salesUsers = [
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.sales@sigap.com',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.sales@sigap.com',
                'phone' => '081234567893',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.sales@sigap.com',
                'phone' => '081234567894',
            ],
            [
                'name' => 'Fitri Handayani',
                'email' => 'fitri.sales@sigap.com',
                'phone' => '081234567895',
            ],
            [
                'name' => 'Gunawan Setiawan',
                'email' => 'gunawan.sales@sigap.com',
                'phone' => '081234567896',
            ],
        ];

        foreach ($salesUsers as $sales) {
            $user = User::create([
                'name' => $sales['name'],
                'email' => $sales['email'],
                'password' => Hash::make('password'),
                'phone' => $sales['phone'],
                'role' => 'sales',
                'is_active' => true,
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(rand(1, 3)),
            ]);
            $user->assignRole('sales');
        }

        // Gudang Users
        $gudangUsers = [
            [
                'name' => 'Hendra Kusuma',
                'email' => 'hendra.gudang@sigap.com',
                'phone' => '081234567897',
            ],
            [
                'name' => 'Indira Sari',
                'email' => 'indira.gudang@sigap.com',
                'phone' => '081234567898',
            ],
            [
                'name' => 'Joko Widodo',
                'email' => 'joko.gudang@sigap.com',
                'phone' => '081234567899',
            ],
        ];

        foreach ($gudangUsers as $gudang) {
            $user = User::create([
                'name' => $gudang['name'],
                'email' => $gudang['email'],
                'password' => Hash::make('password'),
                'phone' => $gudang['phone'],
                'role' => 'gudang',
                'is_active' => true,
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(rand(1, 5)),
            ]);
            $user->assignRole('gudang');
        }

        // Supir Users
        $supirUsers = [
            [
                'name' => 'Krisna Wijaya',
                'email' => 'krisna.supir@sigap.com',
                'phone' => '081234567900',
            ],
            [
                'name' => 'Lukman Hakim',
                'email' => 'lukman.supir@sigap.com',
                'phone' => '081234567901',
            ],
            [
                'name' => 'Maulana Yusuf',
                'email' => 'maulana.supir@sigap.com',
                'phone' => '081234567902',
            ],
            [
                'name' => 'Nugroho Adi',
                'email' => 'nugroho.supir@sigap.com',
                'phone' => '081234567903',
            ],
        ];

        foreach ($supirUsers as $supir) {
            $user = User::create([
                'name' => $supir['name'],
                'email' => $supir['email'],
                'password' => Hash::make('password'),
                'phone' => $supir['phone'],
                'role' => 'supir',
                'is_active' => true,
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(rand(1, 2)),
            ]);
            $user->assignRole('supir');
        }
    }
}
