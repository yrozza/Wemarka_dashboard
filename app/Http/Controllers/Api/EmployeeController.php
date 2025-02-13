<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EmployeeResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Gate::allows('viewAny', Employee::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $employees = User::paginate(10); // Paginate with 10 employees per page

        return EmployeeResource::collection($employees);
    }
    /**
     * Display the specified resource.
     */
    use AuthorizesRequests; // This enables the authorize() method

    public function show($id)
    {
        try {
            $employee = User::findOrFail($id);

            $this->authorize('view', $employee); // This checks the policy

            return new EmployeeResource($employee);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized'], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showByName($employeeName)
    {
        try {
            if (!Gate::allows('viewAny', Employee::class)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            $employees = User::where('first_name', 'LIKE', "%$employeeName%")
            ->orWhere('last_name', 'LIKE', "%$employeeName%")
            ->get();

            if ($employees->isEmpty()) {
                return response()->json([
                    'message' => 'Employee not found'
                ], 404);
            }

            return EmployeeResource::collection($employees);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employee)
    {
        try {
            // Ensure only super_admin can proceed
            

            $validatedData = $request->validate([
                'first_name' => 'sometimes|string|max:255|unique:users,first_name,' . $employee->id,
                'last_name' => 'sometimes|string|max:255|unique:users,last_name,' . $employee->id,
                'email' => 'sometimes|email|unique:users,email,' . $employee->id,
                'phonenumber' => 'sometimes|string|unique:users,phonenumber,' . $employee->id,
                'role' => 'sometimes|nullable|string|max:255|in:super_admin,admin,data_analyst,customer_service,warehouse,employee',
            ]);

            if (!$request->user()->can('updateUser', $employee)) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }


            $employee->update($validatedData);

            

            return response()->json([
                'message' => 'Updated successfully'
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized'], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $employee)
    {
        if (!$employee) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!$request->user()->can('deleteUser', $employee)) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $employee->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }



}
