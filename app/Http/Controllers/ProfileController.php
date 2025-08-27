<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Policies\UserPolicy;
use App\Services\ProfileService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        protected UserPolicy $userPolicy,
    ) {}

    public function update(
        UpdateProfileRequest $request,
        ProfileService $profileService,
        User $user
    ) {
        $authenticatedUser = User::findOrFail(Auth::id());

        $validatedData = $request->validated();

        return $profileService->update(
            $user,
            $validatedData,
            $authenticatedUser
        );
    }
}
