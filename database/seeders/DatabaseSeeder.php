<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting SIGAP Database Seeding...');

        // Core setup
        $this->command->info('📋 Setting up roles and permissions...');
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Users
        $this->command->info('👥 Creating users...');
        $this->call([
            AdminUserSeeder::class,
            UserSeeder::class,
        ]);

        // Master data
        $this->command->info('📦 Creating products...');
        $this->call([
            ProductSeeder::class,
        ]);

        $this->command->info('🏪 Creating customers...');
        $this->call([
            CustomerSeeder::class,
        ]);

        // Business operations
        $this->command->info('📍 Creating check-ins...');
        $this->call([
            CheckInSeeder::class,
        ]);

        $this->command->info('📋 Creating orders...');
        $this->call([
            OrderSeeder::class,
        ]);

        $this->command->info('🚚 Creating deliveries...');
        $this->call([
            DeliverySeeder::class,
        ]);

        $this->command->info('💰 Creating payments...');
        $this->call([
            PaymentSeeder::class,
        ]);

        $this->command->info('📦 Creating backorders...');
        $this->call([
            BackorderSeeder::class,
        ]);

        $this->command->info('📊 Creating inventory logs...');
        $this->call([
            InventorySeeder::class,
        ]);

        $this->command->info('✅ SIGAP Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('🔑 Default Login Credentials:');
        $this->command->info('Admin: admin@sigap.com / password');
        $this->command->info('Sales: ahmad.sales@sigap.com / password');
        $this->command->info('Gudang: hendra.gudang@sigap.com / password');
        $this->command->info('Supir: krisna.supir@sigap.com / password');
    }
}
