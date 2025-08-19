<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        protected UserPolicy $userPolicy,
    ) {}

    public function update(
        UpdateProfileRequest $request,
        User $user
    ) {
        $requester = Auth::user();

        if (! $this->userPolicy->update($requester, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to change this user\'s profile.'
            ]);
        }

        $validated = $request->validated();

        $user->profile->update($validated);
        $user->profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
        ], 200);
    }
}
