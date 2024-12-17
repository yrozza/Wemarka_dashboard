<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EmployeeResource;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return EmployeeResource::collection(Employee::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'Employee_name' => 'required|string|max:255|unique:employees,Employee_name',
                'Employee_email' => 'required|email|unique:employees,Employee_email',
                'Employee_phonenumber' => 'required|string|unique:employees,Employee_phonenumber',
                'Employee_role' => 'required|string|max:255',
            ]);

            $employee = new Employee;
            $employee->Employee_name = $validated['Employee_name'];
            $employee->Employee_email = $validated['Employee_email'];
            $employee->Employee_phonenumber = $validated['Employee_phonenumber'];
            $employee->Employee_role = $validated['Employee_role'];
            $employee->save();

            return response()->json([
                'Message' => 'Created Successfully',
                'data' => $employee
            ],201);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employee = Employee::find($id);
        try {
            if(!$employee){
                return response()->json([
                    'Message' => 'Employee not found'
                ]);
            }
            return response()->json([
                'data' => $employee
            ]);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }

        
    }

    public function showByName($Employee_name){
        $employee = Employee::where('Employee_name','LIKE', "%$Employee_name%")->get();
        try {
            if($employee->isEmpty()){
                return response()->json([
                    'Message' => 'Employee not found'
                ],404);
            }
            return EmployeeResource::collection($employee);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {   
        try {
            $employee->update(
                $request->validate([
                    'Employee_name' => 'required|string|max:255|unique:employees,Employee_name',
                    'Employee_email' => 'required|email|unique:employees,Employee_email',
                    'Employee_phonenumber' => 'required|string|unique:employees,Employee_phonenumber',
                    'Employee_role' => 'required|string|max:255',
                ])
            );

            return response()->json([
                'Message' => 'Updated sucessfully'
            ]);
    } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json([
            'Message' => 'Deleted successfully'
        ]);
    }
}
