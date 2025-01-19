<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
        ]);

        // Check if email already exists
        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'An account with this email already exists.',
            ], 422); // Unprocessable Entity
        }

        // Check if phone number already exists (if provided)
        if (!empty($validated['phone_number']) && User::where('phone_number', $validated['phone_number'])->exists()) {
            return response()->json([
                'message' => 'An account with this phone number already exists.',
            ], 422); // Unprocessable Entity
        }

        // Create the user
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
            'role' => $validated['role'] ?? 'employee', // Default role
        ]);

        // Create a token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the response
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Login an existing user
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The provided crediential is not valid'], 401);
        }

        $token = $user->createToken('YourAppName')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }





    public function getUser(Request $request)
    {
        try {
            // Validate that the user is authenticated
            $validator = Validator::make($request->all(), []);

            // Check if the user is authenticated
            $user = $request->user();
            if (!$user) {
                // If user is not authenticated, throw a ValidationException
                throw new ValidationException($validator);
            }

            return response()->json([
                'user' => $user
            ]);
        } catch (ValidationException $e) {
            // Catch the ValidationException and return a 403 Forbidden response
            return response()->json(['message' => 'Forbidden'], 403);
        }
    }



    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


    
}
