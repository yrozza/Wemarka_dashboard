<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignRole(Request $request, $userId)
    {
        $user = User::find(21);
        $role = Role::where('name', 'super_admin')->where('guard_name', 'api')->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($role) {
            $user->assignRole($role->name); // Use the name, not the object
            return response()->json([
                'message' => 'Role assigned successfully',
                'roles' => $user->getRoleNames() // Retrieve roles
            ]);
        } else {
            return response()->json(['message' => 'Role not found'], 404);
        }
    }
}


