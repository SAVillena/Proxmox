<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $manageUsersPermission = Permission::create(['name' => 'manage users']);
        $manageClusterPermission = Permission::create(['name' => 'manage cluster']);
        $viewClusterPermission = Permission::create (['name' => 'view cluster']);
        $adminRole->givePermissionTo($manageUsersPermission);
        $adminRole->givePermissionTo($manageClusterPermission);
        $adminRole->givePermissionTo($viewClusterPermission);
        $userRole ->givePermissionTo($viewClusterPermission);
    }

}
