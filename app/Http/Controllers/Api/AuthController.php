<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Authenticate a user from the users table and start a session.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $user = User::query()->where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return ApiResponse::error('Invalid credentials.', [
                'email' => ['The provided credentials are incorrect.'],
            ], 422);
        }

        if (!$user->isActive()) {
            return ApiResponse::error('Your account is inactive.', [
                'email' => ['This account is inactive.'],
            ], 403);
        }

        Auth::login($user);
        $user->forceFill(['last_login' => now()])->save();
        $request->session()->regenerate();

        return ApiResponse::success('Login successful.', [
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    /**
     * End the authenticated session.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ApiResponse::success('Logged out successfully.');
    }

    /**
     * Return the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return ApiResponse::success(
            'Authenticated user retrieved successfully.',
            (new UserResource($request->user()))->resolve()
        );
    }
}
