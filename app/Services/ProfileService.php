<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Log;

class ProfileService
{
    public function __construct(
        protected UserPolicy $userPolicy,
    ) {}

    public function update(
        User $user,
        array $data,
        User $requester
    ) {
        Log::info('ProfileService@update: ' . $user->id . '|' . $requester->id);
        
        if (! $this->userPolicy->update($requester, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this user.',
            ], 403);
        }

        $user->profile->update($data);

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user)
        ], 200);
    }
}
