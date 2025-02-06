<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EmployeeResource;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return EmployeeResource::collection(User::all());
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $employee = User::find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee not found'
                ], 404);
            }

            return new EmployeeResource($employee);
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
            $validatedData = $request->validate([
                'first_name' => 'sometimes|string|max:255|unique:users,first_name,' . $employee->id,
                'last_name' => 'sometimes|string|max:255|unique:users,last_name,' . $employee->id,
                'email' => 'sometimes|email|unique:users,email,' . $employee->id,
                'phonenumber' => 'sometimes|string|unique:users,phonenumber,' . $employee->id,
                'role' => 'sometimes|string|max:255',
            ]);

            $employee->update($validatedData);

            return response()->json([
                'message' => 'Updated successfully'
            ]);
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
    public function destroy(User $employee)
    {
        $employee->delete();
        return response()->json([
            'Message' => 'Deleted successfully'
        ]);
    }
}
