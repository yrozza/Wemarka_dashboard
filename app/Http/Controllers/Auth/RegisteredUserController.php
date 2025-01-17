<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'phone_number' => ['required', 'string', 'max:15', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['nullable', 'string'],
            ]);
            // Ensure only one admin exists
            $role = $request->role ? $request->role : 'employee';
            if ($role === 'admin' && User::where('role', 'admin')->exists()) {
                return response()->json(['message' => 'There can only be one admin.'], 400);
            }

            // Create the user, including first_name, last_name, phone_number, and role
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'role' => $role, // Set the role
            ]);

            // Fire the Registered event
            event(new Registered($user));

            // Log the user in immediately
            Auth::login($user);

            // Return a success response with 200 OK status
            return response()->json([
                'message' => 'User created successfully!',
                'user' => $user,
            ], 204); // 200 OK status
        } catch (ValidationException $e) {
            // Return custom validation error messages
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity status
        } catch (\Exception $e) {
            // Handle any other exceptions with a custom message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error status
        }
    }
}
