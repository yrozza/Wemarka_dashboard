<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            Gate::authorize('viewAny', Brand::class);
            return BrandResource::collection(Brand::paginate(10)); // Paginate with 10 items per page
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
            Gate::authorize('create' , Brand::class);
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
                'data' => new BrandResource($brand)
            ],201);
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
            Gate::authorize('view', $brand);
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
            Gate::authorize('viewAny', Brand::class);

            // Query to find brands by name using LIKE with pagination
            $brands = Brand::where('Brand_name', 'LIKE', "%$brand_name%")->paginate(10);

            // Check if no results are found
            if ($brands->isEmpty()) {
                return response()->json([
                    'message' => 'Brand not found',
                ], 404);
            }

            // Return the paginated brand data
            return BrandResource::collection($brands);
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
            Gate::authorize('update', $brand);
            $validated = $request->validate([
                'Brand_name' => 'sometimes|string|max:255|unique:brands,Brand_name,' . $brand->id,
                'Company_address' => 'sometimes|string|max:255',
                'Active' => 'sometimes|boolean'
            ]);

            $brand->update($validated);

            if (!$brand->wasChanged()) {
                return response()->json([
                    'message' => 'Nothing has changed'
                ]);
            }

            return response()->json([
                'Message' => 'Updated successfully',
                'data' => new BrandResource($brand)
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
        try {
            Gate::authorize('delete',$brand);
            $brand->delete();

            return response()->json([
                'Message' => 'Deleted sucessfully'
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }

}
}
