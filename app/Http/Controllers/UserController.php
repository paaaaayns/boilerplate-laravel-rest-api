<?php

namespace App\Http\Controllers;

use App\Enums\Permissions\UserPermissionEnum;
use App\Services\UserService;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\ListUsersRequest;
use App\Http\Requests\User\UpdateUserPasswordRequest;
use App\Http\Requests\User\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function index(
        ListUsersRequest $request,
        UserService $userService
    ) {
        $authenticatedUser = User::findOrFail(Auth::id());

        Log::info('UserController@index called by user: ' . $authenticatedUser);

        $validated = $request->validated();

        return $userService->list(
            $validated['page'] ?? 1,
            $validated['per_page'] ?? 30,
            $authenticatedUser
        );
    }

    public function store(
        StoreUserRequest $request,
        UserService $userService
    ) {
        $authenticatedUser = User::find(Auth::id());

        $validated = $request->validated();

        return $userService->store(
            $validated,
            $authenticatedUser
        );
    }

    public function show(
        UserService $userService,
        User $user
    ) {
        $authenticatedUser = User::find(Auth::id());

        return $userService->show(
            $user,
            $authenticatedUser
        );
    }

    public function update(
        UpdateUserRequest $request,
        UserService $userService,
        User $user
    ) {
        $authenticatedUser = User::find(Auth::id());

        $validated = $request->validated();

        return $userService->update(
            $validated,
            $user,
            $authenticatedUser
        );
    }

    public function updatePassword(
        UpdateUserPasswordRequest $request,
        UserService $userService,
        User $user
    ) {
        $authenticatedUser = Auth::user();

        $validated = $request->validated();

        return $userService->update(
            $validated,
            $user,
            $authenticatedUser
        );
    }

    public function updateRole(
        UpdateUserRoleRequest $request,
        UserService $userService,
        User $user
    ) {
        $authenticatedUser = Auth::user();

        $validated = $request->validated();

        return $userService->update(
            $validated,
            $user,
            $authenticatedUser
        );
    }

    public function destroy(
        UserService $userService,
        User $user
    ) {
        $authenticatedUser = Auth::user();

        return $userService->destroy(
            $user,
            $authenticatedUser
        );
    }

    public function restore(
        UserService $userService,
        string $userId,
    ) {
        $authenticatedUser = User::find(Auth::id());

        return  $userService->restore(
            $userId,
            $authenticatedUser
        );
    }
}
