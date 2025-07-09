<?php

namespace App\Enums\Permissions;

enum PermissionPermissionEnum: string
{
    case ViewAny = 'view any permission';
    case ViewOwn = 'view own permission';
    case ViewProtectedData = 'view permission protected data';
}
