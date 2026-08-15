<?php

namespace Database\Seeders;

use App\Enums\Permission as AppPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create or update permissions
        foreach (AppPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        // Create Super Admin role with all permissions
        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $superAdminRole->syncPermissions(AppPermission::values());
    }
}
