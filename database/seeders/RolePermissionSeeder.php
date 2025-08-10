<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $salesRole = Role::create(['name' => 'sales']);
        $gudangRole = Role::create(['name' => 'gudang']);
        $supirRole = Role::create(['name' => 'supir']);

        // Create Permissions
        $permissions = [
            // User Management
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Customer Management
            'manage-customers',
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // Product Management
            'manage-products',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Order Management
            'manage-orders',
            'view-orders',
            'create-orders',
            'edit-orders',
            'confirm-orders',
            'cancel-orders',

            // Check-in Management
            'manage-checkins',
            'view-checkins',
            'create-checkins',

            // Delivery Management
            'manage-deliveries',
            'view-deliveries',
            'assign-deliveries',
            'update-delivery-status',

            // K3 Checklist
            'manage-k3',
            'view-k3',
            'create-k3',

            // Payment Management
            'manage-payments',
            'view-payments',
            'create-payments',
            'update-payments',

            // Backorder Management
            'manage-backorders',
            'view-backorders',

            // Reporting
            'view-reports',
            'export-reports',

            // Dashboard
            'view-admin-dashboard',
            'view-sales-dashboard',
            'view-gudang-dashboard',
            'view-supir-dashboard',

            // Activity Logs
            'view-activity-logs',

            // Override/Manual Control
            'override-status',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign Permissions to Roles

        // Admin - Full Access
        $adminRole->givePermissionTo(Permission::all());

        // Sales Permissions
        $salesRole->givePermissionTo([
            'view-sales-dashboard',
            'manage-customers',
            'view-customers',
            'create-customers',
            'edit-customers',
            'manage-checkins',
            'view-checkins',
            'create-checkins',
            'manage-orders',
            'view-orders',
            'create-orders',
            'edit-orders',
            'manage-payments',
            'view-payments',
            'create-payments',
            'update-payments',
            'view-reports',
        ]);

        // Gudang Permissions
        $gudangRole->givePermissionTo([
            'view-gudang-dashboard',
            'manage-products',
            'view-products',
            'create-products',
            'edit-products',
            'view-orders',
            'confirm-orders',
            'manage-deliveries',
            'view-deliveries',
            'assign-deliveries',
            'manage-backorders',
            'view-backorders',
            'view-reports',
        ]);

        // Supir Permissions
        $supirRole->givePermissionTo([
            'view-supir-dashboard',
            'view-deliveries',
            'update-delivery-status',
            'manage-k3',
            'view-k3',
            'create-k3',
        ]);
    }
}
