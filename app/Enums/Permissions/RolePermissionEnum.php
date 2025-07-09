<?php

namespace App\Enums\Permissions;

enum RolePermissionEnum: string
{
    case ViewAny = 'view any role';
    case ViewOwn = 'view own role';
    case ViewProtectedData = 'view role protected data';
    case Create = 'create role';
    case Update = 'update role';
    case HardDelete = 'hard delete role';
}
