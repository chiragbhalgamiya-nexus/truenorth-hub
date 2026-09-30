<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            new ProfileResource($user),
        ], Response::HTTP_OK);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  UpdateProfileRequest  $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update($request->validated());

        return response()->json([
            new ProfileResource($user),
        ], Response::HTTP_OK);
    }

    /**
     * Change the authenticated user's password.
     *
     * @param  ChangePasswordRequest  $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all tokens except current
        $user->tokens()->where('id', '!=', auth()->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Password updated successfully.',
        ], Response::HTTP_OK);
    }
}
