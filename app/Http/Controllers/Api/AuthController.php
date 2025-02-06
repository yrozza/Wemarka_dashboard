<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'nullable|string|max:255',
                'role' => 'nullable|string|max:255',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Add validation for the image
            ]);

            // Handle the file upload
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                // Store the file in the 'profile_pics' folder
                $profilePicPath = $request->file('profile_pic')->store('profile_pics', 'public');
            }

            // Insert the user record
            DB::table('users')->insert([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone_number' => $validated['phone_number'] ?? null,
                'role' => $validated['role'] ?? 'employee',
                'profile_pic' => $profilePicPath,  // Save the file path in the database
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Retrieve the newly inserted user
            $user = DB::table('users')->where('email', $validated['email'])->first();

            // Generate a URL to access the stored file
            $profilePicUrl = $profilePicPath ? Storage::url($profilePicPath) : null;

            return response()->json([
                'user' => $user,
                'profile_pic_url' => $profilePicUrl,
                'message' => 'User registered successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error registering user', 'error' => $e->getMessage()], 500);
        }
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
        // Ensure the user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        // Get the user profile picture URL
        $profilePicUrl = $request->user()->profile_pic ? Storage::url($request->user()->profile_pic) : 'Not provided';

        return response()->json([
            'first_name' => $request->user()->first_name,
            'last_name' => $request->user()->last_name,
            'role' => $request->user()->role,
            'email' => $request->user()->email,
            'profile_pic' => $profilePicUrl,  // Add profile picture to response
        ]);
    }



    public function updateProfile(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        // Validation rules
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|string|max:20',
            'role'=>'sometimes|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user fields if provided
        $user->update($request->only(['first_name', 'last_name', 'email', 'phone_number','role']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role, // Role should not be editable by users
                'email' => $user->email,
            ]
        ]);
    }


    public function updateProfilePic(Request $request)
    {
        $user = $request->user(); // Get the authenticated user

        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        // Validate the request
        $request->validate([
            'profile_pic' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048', // Ensure valid image file
        ]);

        // Check if a new profile picture was uploaded
        if ($request->hasFile('profile_pic')) {
            // Get the file and store it
            $file = $request->file('profile_pic');
            $profilePicPath = $file->store('profile_pics', 'public');

            // Remove the old profile picture from storage if it exists
            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }

            // Update the database with the new profile picture URL
            DB::table('users')
            ->where('id', $user->id)
                ->update(['profile_pic' => $profilePicPath]);

            return response()->json(['message' => 'Profile picture updated successfully']);
        }

        return response()->json(['message' => 'No file uploaded'], 422);
    }






    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


    public function changePassword(Request $request)
    {
        try {
            $user = $request->user(); // Get the authenticated user

            // Validate the request
            $request->validate([
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string|min:8',
            ]);

            // Prevent using the same old password
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json(['message' => 'You just put the old password, try again with a new one.'], 422);
            }

            // Update the password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json(['message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'message' => 'An error occurred while changing the password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
}
