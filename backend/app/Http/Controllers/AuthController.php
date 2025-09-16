<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Auth\Events\Validated;

class AuthController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function authenticate(Request $request) : JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' =>['required','email'],
                'password' => ['required','min:8']
            ]);

            if(Auth::attempt($credentials)) {
                $user = Auth::user();
                return response()->json([
                    'message' => "User authenticated successfully",
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ],
                    'token' => $user->createToken('auth-token')->plainTextToken
                ], 200);
            }
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['These credentials do not match our records.']
                ]
            ], 401);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => "Validation failed",
                'errors'  => [
                    'email' => ["These credentials do not match our records."],
                ]
            ], 401);
        }
    }

    public function register(Request $request) : JsonResponse {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
                'token' => $user->createToken('auth-token')->plainTextToken
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => "Validation failed",
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An error occurred",
                "error" => $e->getMessage()
            ], 501);
        }
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke the current user's token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
