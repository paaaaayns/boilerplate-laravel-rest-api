<?php

namespace App\Services;

use Exception;
use App\Models\Role;
use App\Enums\RoleEnum;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class RoleService
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_PER_PAGE = 10;

    public function listQuery()
    {
        return QueryBuilder::for(Role::class)
            ->allowedIncludes([
                'permissions',
            ])
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts([
                AllowedSort::field('name'),
                AllowedSort::field('created_at'),
                AllowedSort::field('deleted_at'),
                AllowedSort::field('updated_at'),
            ])
            ->defaultSort('name')
            ->select('roles.*');
    }

    public function list(
        bool $isSuperAdmin = false,
        int $page = self::DEFAULT_PAGE,
        int $perPage = self::DEFAULT_PER_PAGE,
    ) {
        $roles = $this
            ->listQuery()
            ->when(
                !$isSuperAdmin,
                function ($query) {
                    $query->where('name', '!=', RoleEnum::SuperAdmin->value);
                }
            )
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return RoleResource::collection($roles)
            ->response()
            ->getData(true);
    }

    public function showOrFail(string $roleId)
    {
        $role = QueryBuilder::for(Role::class)
            ->allowedIncludes([
                'permissions',
            ])
            ->findOrFail($roleId);

        return new RoleResource($role);
    }


    public function storeOrFail(
        string $name,
        string $guardName = "web"
    ) {
        try {
            return Role::create([
                'name' => $name,
                'guard_name' => $guardName
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage() ?? 'Failed to create role. Please try again.');
        }
    }
    public function updateOrFail(
        array $data,
        string $roleId
    ) {
        $role = $this->showOrFail($roleId);

        $updateData =  Arr::except(
            $data,
            [
                'permissions'
            ]
        );

        $permissions = $data['permissions'] ?? [];

        $role->update($updateData);

        $role->syncPermissions($permissions);

        return $role->updateOrFail($data);
    }

    public function destroyOrFail(string $roleId)
    {
        $role = $this->showOrFail($roleId);

        if ($role->users()->exists()) {
            abort(409, 'Cannot delete role with existing references.');
        }

        return $role->deleteOrFail();
    }
}
