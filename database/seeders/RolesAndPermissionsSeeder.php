<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view users',
            'edit users',
            'delete users',
            'view articles',
            'edit articles',
            'delete articles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign created permissions

        // Admin role - gets all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Editor role - can view, edit, and delete articles
        $editorRole = Role::create(['name' => 'editor']);
        $editorRole->givePermissionTo([
            'view articles',
            'edit articles',
            'delete articles',
        ]);

        // Viewer role - can only view articles
        $viewerRole = Role::create(['name' => 'viewer']);
        $viewerRole->givePermissionTo('view articles');
    }
}
