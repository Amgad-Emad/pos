<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view-dashboard',
            'view-inventory',
            'view-wholesale-price',
            'manage-sales',
            'manage-returns',
            'view-invoices',
            'manage-products',
            'manage-categories',
            'manage-suppliers',
            'manage-purchases',
            'manage-users',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions($permissions);

        $seller = Role::findOrCreate('seller');
        $seller->syncPermissions([
            'view-inventory',
            'manage-sales',
            'manage-returns',
            'view-invoices',
        ]);
    }
}
