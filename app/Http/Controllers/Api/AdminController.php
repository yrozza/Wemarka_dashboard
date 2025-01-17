<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to the Admin Dashboard!',
        ]);
    }

    /**
     * Create a new user.
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string', // Add role if needed
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role ?? 'employee', // Default to 'employee'
        ]);

        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user,
        ]);
    }

    /**
     * Delete a user.
     */
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully!',
        ]);
    }
}
