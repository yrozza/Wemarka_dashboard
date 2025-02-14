<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\Supplier;
use App\Http\Resources\SupplierResource;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            Gate::authorize('viewAny', Supplier::class);

            $suppliers = Supplier::paginate(10); // Adjust the pagination limit as needed

            return SupplierResource::collection($suppliers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Gate::authorize('create', Supplier::class);

            $validatedData = $request->validate([
                'Supplier_name' => 'required|string|max:255|unique:suppliers,Supplier_name',
                'Address' => 'required|string|max:255',
                'Phonenumber' => 'required|string|max:15|unique:suppliers,Phonenumber',
                'Status' => 'required|boolean', // Accepts true/false or 1/0
            ]);

            $supplier = Supplier::create($validatedData);

            return response()->json([
                'message' => 'Supplier created successfully',
                'data' => new SupplierResource($supplier)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $supplier = Supplier::find($id);

            if (!$supplier) {
                return response()->json(['message' => 'Supplier not found'], 404);
            }

            Gate::authorize('view', $supplier);

            return new SupplierResource($supplier);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showByName($supplier_name)
    {

        try {
            Gate::authorize('viewAny', Supplier::class);

            $suppliers = Supplier::where('Supplier_name', 'LIKE', "%$supplier_name%")->paginate(10);

            if ($suppliers->isEmpty()) {
                return response()->json(['message' => 'Supplier not found'], 404);
            }

            return SupplierResource::collection($suppliers);
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
    public function update(Request $request, Supplier $supplier)
    {
        try {
            Gate::authorize('update', $supplier);

            $validatedData = $request->validate([
                'Supplier_name' => 'sometimes|required|string|max:255|unique:suppliers,Supplier_name,' . $supplier->id,
                'Address' => 'sometimes|required|string|max:255',
                'Phonenumber' => 'sometimes|required|string|max:15|unique:suppliers,Phonenumber,' . $supplier->id,
                'Status' => 'sometimes|required|boolean',
            ]);

            $supplier->update($validatedData);

            if (!$supplier->wasChanged()) {
                return response()->json([
                    'message' => 'No changes were made.',
                ]);
            }

            return response()->json([
                'message' => 'Supplier updated successfully',
                'data' => new SupplierResource($supplier)
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
    public function destroy(Supplier $supplier)
    {
        try {
            Gate::authorize('delete', $supplier);

            if (!$supplier) {
                return response()->json([
                    'message' => 'Supplier not found'
                ], 404);
            }

            $supplier->delete();

            return response()->json([
                'message' => 'Supplier deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }
}
