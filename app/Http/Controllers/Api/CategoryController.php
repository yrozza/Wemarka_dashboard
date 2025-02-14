<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            Gate::authorize('viewAny', Category::class);

            // Paginate the categories (default 10 per page, can be adjusted via ?per_page=)
            $categories = Category::paginate($request->query('per_page', 10));

            return CategoryResource::collection($categories)->response();
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
            Gate::authorize('create', Category::class);
            $validated = $request->validate([
                'Category' => 'required|unique:categories,Category|max:255',
                'Active' => 'required|boolean'
            ]);
            $Categorys = new Category();
            $Categorys->Category=$validated['Category'];
            $Categorys->Active=$validated['Active'];
            $Categorys->save();

            return new CategoryResource($Categorys); 
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showByName($category)
    {
        try {
            Gate::authorize('viewAny', Category::class);

            // Query to find categories by name using LIKE with pagination
            $categories = Category::where('Category', 'LIKE', "%$category%")->paginate(10);

            // Check if no results are found
            if ($categories->isEmpty()) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            // Return the paginated category data
            return CategoryResource::collection($categories);
        } catch (\Exception $e) {
            // Handle internal server errors
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
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'Message' => 'Category not found'
                ], 404);
            }

            Gate::authorize('view', $category);

            return new CategoryResource($category);
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
    public function update(Request $request, Category $category)
    {
        try {
            // Authorization check using policy
            Gate::authorize('update', $category);

            // Validate only provided fields (supports PATCH for partial updates)
            $validated = $request->validate([
                'Category' => 'sometimes|required|unique:categories,Category,' . $category->id . '|max:255',
                'Active' => 'sometimes|required|boolean',
            ]);

            // Update the category
            $category->update($validated);

            // If no changes were made, return a message
            if (!$category->wasChanged()) {
                return response()->json([
                    'message' => 'Nothing has changed'
                ]);
            }

            return response()->json([
                'message' => 'Updated successfully',
                'data' => new CategoryResource($category),
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
    public function destroy(Category $category)
    {
        try {
            Gate::authorize('delete', $category);
            $category->delete();

            return response()->json([
                'Message' => 'Deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
