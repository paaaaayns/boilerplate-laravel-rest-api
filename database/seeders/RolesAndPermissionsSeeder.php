<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Enums\RoleEnum;
use App\Enums\Permissions\PermissionPermissionEnum;
use App\Enums\Permissions\RolePermissionEnum;
use App\Enums\Permissions\UserPermissionEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {
            foreach (UserPermissionEnum::cases() as $permission) {
                Permission::create(['name' => $permission->value, 'category' => 'user']);
            }

            foreach (RolePermissionEnum::cases() as $permission) {
                Permission::create(['name' => $permission->value, 'category' => 'role']);
            }

            foreach (PermissionPermissionEnum::cases() as $permission) {
                Permission::create(['name' => $permission->value, 'category' => 'permission']);
            }
        });


        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::create(['name' =>  RoleEnum::SuperAdmin->value])
            ->givePermissionTo([
                UserPermissionEnum::ViewAny->value,
                UserPermissionEnum::ViewOwn->value,
                UserPermissionEnum::ViewProtectedData->value,
                UserPermissionEnum::Create->value,
                UserPermissionEnum::Update->value,
                UserPermissionEnum::SoftDelete->value,
                UserPermissionEnum::HardDelete->value,
                UserPermissionEnum::Restore->value,

                RolePermissionEnum::ViewAny->value,
                RolePermissionEnum::ViewOwn->value,
                RolePermissionEnum::ViewProtectedData->value,
                RolePermissionEnum::Create->value,
                RolePermissionEnum::Update->value,
                RolePermissionEnum::HardDelete->value,

                PermissionPermissionEnum::ViewAny->value,
                PermissionPermissionEnum::ViewOwn->value,
                PermissionPermissionEnum::ViewProtectedData->value,
            ]);

        Role::create(['name' => RoleEnum::Admin->value])
            ->givePermissionTo([
                UserPermissionEnum::ViewAny->value,
                UserPermissionEnum::ViewOwn->value,
                UserPermissionEnum::ViewProtectedData->value,
                UserPermissionEnum::Create->value,
                UserPermissionEnum::Update->value,
                UserPermissionEnum::SoftDelete->value,
            ]);

        Role::create(['name' => RoleEnum::Employee->value])
            ->givePermissionTo([
                UserPermissionEnum::ViewOwn->value,
                UserPermissionEnum::Update->value,
            ]);

        Role::create(['name' => RoleEnum::User->value])
            ->givePermissionTo([
                UserPermissionEnum::ViewOwn->value,
                UserPermissionEnum::Update->value,
            ]);
    }
}
