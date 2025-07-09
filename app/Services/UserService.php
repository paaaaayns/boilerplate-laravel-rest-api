<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Imports\UsersImport;
use App\Exports\UsersExport;
use App\Exports\UserImportTemplate;
use App\Filters\RoleFilter;
use App\Filters\DateRangeFilter;
use App\Filters\FullnameFilter;
use App\Filters\PermissionFilter;
use App\Http\Resources\UserResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_PER_PAGE = 10;

    public function listQuery()
    {
        $query = QueryBuilder::for(User::class)
            ->allowedIncludes([
                'profile',
                'roles',
                'permissions',
            ])
            ->allowedFilters([
                AllowedFilter::partial('id'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('last_name', 'profiles.last_name'),
                AllowedFilter::custom('full_name', new FullnameFilter()),
                AllowedFilter::partial('first_name', 'profiles.first_name'),
                AllowedFilter::partial('middle_name', 'profiles.middle_name'),
                AllowedFilter::partial('contact_number', 'profiles.contact_number'),
                AllowedFilter::custom('created_on', new DateRangeFilter, 'users.created_at'),
                AllowedFilter::custom('updated_on', new DateRangeFilter, 'users.updated_at'),
                AllowedFilter::custom('deleted_on', new DateRangeFilter, 'users.deleted_at'),
                AllowedFilter::custom('role', new RoleFilter()),
                AllowedFilter::custom('permission', new PermissionFilter()),
                AllowedFilter::trashed()
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                AllowedSort::field('email', 'email'),
                AllowedSort::field('created_on', 'created_at'),
                AllowedSort::field('updated_on', 'updated_at'),
                AllowedSort::field('deleted_on', 'deleted_at'),
                AllowedSort::field('first_name',  'profiles.first_name'),
                AllowedSort::field('middle_name',  'profiles.middle_name'),
                AllowedSort::field('contact_number',  'profiles.contact_number'),
                AllowedSort::field('last_name',  'profiles.last_name'),
            ])
            ->select('users.*')
            ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id');

        Log::debug('UserService listQuery executed', [
            'query' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        return $query;
    }

    public function list(
        int $page = self::DEFAULT_PAGE,
        int $perPage = self::DEFAULT_PER_PAGE,
    ) {
        $users = $this
            ->listQuery()
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return UserResource::collection($users)
            ->response()
            ->getData(true);
    }

    public function listExport(
        int $page = self::DEFAULT_PAGE,
        int $perPage = self::DEFAULT_PER_PAGE,
    ) {
        return $this
            ->listQuery()
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);
    }

    public function show(string $userId)
    {
        $user = QueryBuilder::for(User::class)
            ->allowedIncludes([
                'roles',
                'permissions',
                'profile',
            ])
            ->findOrFail($userId);

        return new UserResource($user);
    }

    public function store(array $data, $requester)
    {
        $roles = $data['roles'] ?? [];

        if (
            Arr::where(
                $roles,
                fn($role) => $role === RoleEnum::SuperAdmin->value
            ) &&
            ! $requester->hasRole(RoleEnum::SuperAdmin->value)
        ) {
            return [
                'success' => false,
                'message' => 'Insufficient permissions: You are not authorized to add a Super Admin.'
            ];
        }

        $user = DB::transaction(function () use ($data, $roles) {
            $createdUser = User::create(
                [
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ]
            );

            $createdUser->profile()->create(
                Arr::only(
                    $data,
                    [
                        'first_name',
                        'middle_name',
                        'last_name',
                        'contact_number',
                        'gender'
                    ]
                )
            );

            $createdUser->syncRoles($roles);

            return $createdUser;
        });

        return $user;
    }


    public function update(array $updateData, string $userId)
    {
        $user = User::findOrFail($userId);

        return DB::transaction(function () use ($user, $updateData) {
            if (isset($updateData['roles'])) {
                $user->syncRoles($updateData['roles']);
            }

            return $user->updateOrFail($updateData);
        });
    }


    public function destroy(string $userId)
    {
        $user = User::findOrFail($userId);

        return $user->deleteOrFail();
    }

    public function restore(string $userId)
    {
        $user = User::onlyTrashed()->findOrFail($userId);

        return $user->restore();
    }
}
