<?php
/**
 * Created by hasak on 29.11.25 @ 17:57
 **/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Roles are the unit of authorization here — 'admin' reaches the
        // Filament panel, 'user' does not. No individual permissions are
        // defined, so there is nothing to sync onto the admin role.
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);
    }
}
