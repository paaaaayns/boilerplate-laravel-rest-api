<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SuperAdmin = 'super-admin';
    case Manager = 'manager';
    case Tenant = 'tenant';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            static::SuperAdmin => 'Super admins',
            static::Manager => 'Managers',
            static::Tenant => 'Tenants',
            static::User => 'Users',
        };
    }
}
