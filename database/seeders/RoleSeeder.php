<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // create permissions
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage settings']);
        Permission::firstOrCreate(['name' => 'clinical functions']);
        Permission::firstOrCreate(['name' => 'manage appointments']);
        Permission::firstOrCreate(['name' => 'support patients']);

        // create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'adm']);
        $admin->givePermissionTo(['manage users', 'manage settings']);

        $medico = Role::firstOrCreate(['name' => 'medico']);
        $medico->givePermissionTo(['clinical functions', 'manage appointments']);

        $enfermeiro = Role::firstOrCreate(['name' => 'enfermeiro']);
        $enfermeiro->givePermissionTo(['support patients']);
    }
}
