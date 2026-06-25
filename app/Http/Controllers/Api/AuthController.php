<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Creates a new user account and returns an API token.
     *
     * @group Authentication
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password (min 8 characters). Example: password123
     * @bodyParam password_confirmation string required Must match password. Example: password123
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "created_at": "22/06/2026"
     *   },
     *   "token": "1|abc123def456...",
     *   "token_type": "Bearer"
     * }
     * @response 422 {
     *   "message": "The email field is required. (and 2 more errors)",
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."],
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Log in an existing user
     *
     * Authenticates a user with email and password, returns an API token.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "created_at": "22/06/2026"
     *   },
     *   "token": "1|abc123def456...",
     *   "token_type": "Bearer"
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     * @response 422 {
     *   "message": "The email field is required. (and 1 more error)",
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."]
     *   }
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Log out the current user
     *
     * Revokes the current Bearer token.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logged out successfully"
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
