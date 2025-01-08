<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Validation\Rule;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductResource::collection(Product::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'Product_name' => 'required|string|unique:products,Product_name|max:255',
                'Product_description' => 'required|string',
                'brand_id' => [
                    'required',
                    Rule::exists('brands', 'id')->where(function ($query) {
                        $query->where('active', true);
                    }),
                ],
                'category_id' => [
                    'required',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('active', true);
                    }),
                ],
            ]);
            $product = new Product();
            $product->Product_name = $validated['Product_name'];
            $product->Product_description = $validated['Product_description'];
            $product->brand_id = $validated['brand_id'];
            $product->category_id = $validated['category_id'];
            $product->save();

            return response()->json([
                'Message' => 'Created successfully',
                'data' => new ProductResource($product)
            ]);
        return new ProductResource($product);
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
        $Product = Product::find($id);
        if(!$Product){
            return response()->json([
                'Message' => 'Not found'
            ],404);
        
        }
            return new ProductResource($Product);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
        }

    public function showByName($Product_name)
    {
        try {
            $products = Product::where('Product_name', 'LIKE', "%$Product_name%")->get();
            // Check if no results are found
            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }
            // Return the product data using ProductResource
            return ProductResource::collection($products);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAllProductsWithVariants()
    {
        // Eager load variants for all products
        $products = Product::with('variants')->get();

        return response()->json($products);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'Product_name' => 'required|string|unique:products,Product_name|max:255',
                'Product_description' => 'required|string',
                'brand_id' => [
                    'required',
                    Rule::exists('brands', 'id')->where(function ($query) {
                        $query->where('active', true);
                    }),
                ],
                'category_id' => [
                    'required',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('active', true);
                    }),
                ],
            ]);
            $product->update($validated);

            if (!$product->wasChanged()) {
                // If no changes were made, return a message
                return response()->json([
                    'message' => 'Nothing has changed'
                ]);
            }

            return response()->json([
                'Message' => 'Update sucessfully',
                'data' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function updateOnlyone(Request $request, $id)
    {
        try {
            // Find the product
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'Message' => 'Not found'
                ], 404);
            }

            // Validate the request data
            $validated = $request->validate([
                'Product_name' => 'sometimes|string|unique:products,Product_name|max:255',
                'Product_description' => 'sometimes|string',
                'brand_id' => [
                    'sometimes',
                    Rule::exists('brands', 'id')->where(function ($query) {
                        $query->where('active', true);
                    }),
                ],
                'category_id' => [
                    'sometimes',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('active', true);
                    }),
                ],
            ]);

            // Update the product with validated data
            $product->update($validated);

            // Respond with updated product as a resource
            return new ProductResource($product);
        } catch (\Exception $e) {
            // Catch and respond to any unexpected errors
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            'Message' => 'Deleted sucessfully'
        ]);
    }
}
