<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return BrandResource::collection(Brand::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'Brand_name' => 'required|string|unique:brands,Brand_name|max:255',
                'Company_address' => 'required|string|max:255',
                'Active' => 'required|boolean'
            ]);

            $brand = new Brand();
            $brand->Brand_name = $validated['Brand_name'];
            $brand->Company_address = $validated['Company_address'];
            $brand->Active = $validated['Active'];
            $brand->save();

            return response()->json([
                'Message' => 'Created Successfully',
                'data' => $brand
            ]);
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
    public function show($id)
    {
        try {
            $brand = Brand::find($id);

            if (!$brand) {
                return response()->json([
                    'message' => 'Not found',
                ], 404); // Return a 404 Not Found status
            }

            return new BrandResource($brand);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showByName($brand_name)
    {
        try {
            // Query to find the brand by name using LIKE
            $brand = Brand::where('Brand_name', 'LIKE', "%$brand_name%")->get();

            // Check if no results are found
            if ($brand->isEmpty()) {
                return response()->json([
                    'message' => 'Brand not found',
                ], 404);
            }

            // Return the brand data
            return BrandResource::collection($brand);
        } catch (\Exception $e) {
            // Handle internal server errors
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        try {
            $validated = $request->validate([
                'Brand_name' => 'required|string|unique:brands,Brand_name|max:255',
                'Company_address' => 'required|string|max:255',
                'Active' => 'required|boolean'
            ]);

            $brand -> update($validated);

            if (!$brand->wasChanged()) {
                // If no changes were made, return a message
                return response()->json([
                    'message' => 'Nothing has changed'
                ]);
            }

            return response()->json([
                'Message' => 'Update sucessfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateOnlyOne(Request $request, $id)
    {
        try {
            $brand = Brand::find($id);
            if (!$brand) {
                return response()->json([
                    'Message' => 'Not found'
                ], 404);
            }
            $validated = $request->validate([
                'Brand_name' => 'sometimes|unique:brands,Brand_name|max:255',
                'Company_address' => 'sometimes|string|max:255',
                'Active' => 'sometimes|boolean'
            ]);
            $brand->update($validated);
            return response()->json([
                'Message' => 'Updated successfully'
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
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json([
            'Message' => 'Deleted sucessfully'
        ]);
    }
}
