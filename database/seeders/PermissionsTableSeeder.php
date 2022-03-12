<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('permissions')->delete();
        //
        $crudPermissions = ["user", "service", "banner", "category", "subcategory"];
        $fleetManagerPermissions = ["manage-fleet"];
        $adminRolePermissions = ["manager-fleets", "manage-subscriptions"];
        $managerRolePermissions = ["manager-fleets", "my-subscription"];
        $adminManagerRolePermissions = ["view-report", "view-payout", "view-earning","view-subscription"];
        $cityAdminRolePermissions = ["view-report", "view-payout", "view-earning","view-subscription"];

        //creating the permissions
        $allPermissions = array_merge($fleetManagerPermissions, $managerRolePermissions, $adminRolePermissions, $crudPermissions, $adminManagerRolePermissions);
        foreach ($allPermissions as $mPermission) {
            Permission::firstorcreate(['name' => $mPermission]);
        }


        //for fleet manager
        $fleetManagerRole = Role::firstorcreate([
            'name' => 'fleet-manager'
        ], [
            'guard_name' => 'web'
        ]);
        $fleetManagerRole->syncPermissions($fleetManagerPermissions);

        //admin role permissions
        $adminRole = Role::firstorcreate([
            'name' => 'admin'
        ], [
            'guard_name' => 'web'
        ]);
        $permissions = array_merge($adminRolePermissions, $adminManagerRolePermissions);
        $adminRole->syncPermissions($permissions);

        //manager role permissions
        $managerRole = Role::firstorcreate([
            'name' => 'manager'
        ], [
            'guard_name' => 'web'
        ]);
        $permissions = array_merge($managerRolePermissions, $adminManagerRolePermissions);
        $managerRole->syncPermissions($permissions);


        //manager role permissions
        $cityAdminRole = Role::firstorcreate([
            'name' => 'city-admin'
        ], [
            'guard_name' => 'web'
        ]);
        $cityAdminRole->syncPermissions($cityAdminRolePermissions);

        //others
    }
}
