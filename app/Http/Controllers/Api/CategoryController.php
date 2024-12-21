<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
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

    public function showByName($Category){
        try {
            $catogry_name = Category::where('Category', 'LIKE', "%$Category%")->get();
            if($catogry_name->isEmpty()){
                return response()->json([
                    'Message' => 'Category not found'
                ],404);
            }
            return CategoryResource::collection($catogry_name);
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
    public function show( $id)
    {
        try {
            $category = Category::find($id);
            if(!$category){
                return response()->json([
                    'Message' =>  'Category not found'
                ],404);
            }
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
            $validated = $request->validate([
                'Category' => 'required|unique:categories,Category|max:255',
                'Active' => 'required|boolean'
            ]);
            
        $category->update($validated);
            if (!$category->wasChanged()) {
                // If no changes were made, return a message
                return response()->json([
                    'Message' => 'Nothing has changed'
                ]);
            }
            return response()->json([
                'Message' => 'Updated sucessfully'
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
        $category->delete();

        return response()->json([
            'Message' => 'Deleted successfully'
        ]);
    }
}
