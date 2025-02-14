<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Models\Varient;
use App\Policies\ProductPolicy;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            Gate::authorize('viewAny', Product::class);

            return ProductResource::collection(Product::paginate(10));
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
            Gate::authorize('create',Product::class);
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
                'Subcategory' => 'nullable|string|max:255',
                'Origin' => 'nullable|string|max:255',
                'Benefit' => 'nullable|string|max:255',
                'Effect' => 'nullable|string|max:255',
                'Ingredients' => 'nullable|string',
                'Supplier' => 'nullable|string|max:255',
                'Tags' => 'nullable|string|max:255', // Adding validation for Tags
            ]);

            $product = new Product();
            $product->Product_name = $validated['Product_name'];
            $product->Product_description = $validated['Product_description'];
            $product->brand_id = $validated['brand_id'];
            $product->category_id = $validated['category_id'];
            $product->Subcategory = $validated['Subcategory'] ?? null;  // Handle optional fields
            $product->Origin = $validated['Origin'] ?? null;
            $product->Benefit = $validated['Benefit'] ?? null;
            $product->Effect = $validated['Effect'] ?? null;
            $product->Ingredients = $validated['Ingredients'] ?? null;
            $product->Supplier = $validated['Supplier'] ?? null;
            $product->Tags = $validated['Tags'] ?? null;  // Assign Tags value

            // Set Category_name and Brand_name based on related models
            $product->Category_name = Category::find($validated['category_id'])->Category;
            $product->Brand_name = Brand::find($validated['brand_id'])->Brand_name;

            $product->save();

            return response()->json([
                'Message' => 'Created successfully',
                'data' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showByName($Product_name, Request $request)
    {
        try {
            Gate::authorize('viewAny', Product::class);
            // Get the 'per_page' parameter from the request, default to 10 if not present
            $perPage = $request->get('per_page', 10);

            // Paginate results
            $products = Product::where('Product_name', 'LIKE', "%$Product_name%")->paginate($perPage);

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




    public function getAllProductsWithVariants(Request $request)
    {

        try {
            Gate::authorize('viewAny', Product::class);


            $perPage = $request->get('per_page', 10);

            $products = DB::table('products')
            ->leftJoin('varients', 'products.id', '=', 'varients.product_id')
            ->select('products.*', 'varients.*') // Get all columns from both tables
            ->paginate($perPage);

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }



    public function show($id)
    {
        try {
            $product = DB::table('products')
            ->leftJoin('varients', 'products.id', '=', 'varients.product_id')
            ->where('products.id', $id)
            ->select('products.*', 'varients.*') // Select everything from both tables
            ->get();
            Gate::authorize('view', $product);

            if ($product->isEmpty()) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return response()->json($product);
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
    public function update(Request $request, Product $product)
    {
        try {
            Gate::authorize('update', $product);
            $validated = $request->validate([
                'Product_name' => 'required|string|max:255|unique:products,Product_name,' . $product->id,
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
                'Origin' => 'nullable|string',
                'Benefit' => 'nullable|string',
                'Effect' => 'nullable|string',
                'Ingredients' => 'nullable|string',
                'Supplier' => 'nullable|string',
                'Category_name' => 'nullable|string',
                'Brand_name' => 'nullable|string',
                'Tags' => 'nullable|string',
            ]);
                

            // Prepare an array of the attributes to check
            $changes = [
                'Product_name' => $validated['Product_name'],
                'Product_description' => $validated['Product_description'],
                'brand_id' => $validated['brand_id'],
                'category_id' => $validated['category_id'],
                'Origin' => $validated['Origin'] ?? $product->Origin,
                'Benefit' => $validated['Benefit'] ?? $product->Benefit,
                'Effect' => $validated['Effect'] ?? $product->Effect,
                'Ingredients' => $validated['Ingredients'] ?? $product->Ingredients,
                'Supplier' => $validated['Supplier'] ?? $product->Supplier,
                'Category_name' => $validated['Category_name'] ?? $product->Category_name,
                'Brand_name' => $validated['Brand_name'] ?? $product->Brand_name,
                'Tags' => $validated['Tags'] ?? $product->Tags,
            ];

            // Check if any of the values have changed
            $productChanges = false;
            foreach ($changes as $field => $newValue) {
                if ($product->$field !== $newValue) {
                    $productChanges = true;
                    break; // Exit loop early once a change is detected
                }
            }

            if ($productChanges) {
                // Perform the update with Query Builder
                DB::table('products')
                ->where('id', $product->id)
                    ->update(array_merge($changes, ['updated_at' => now()]));

                return response()->json([
                    'Message' => 'Updated successfully',
                    'data' => DB::table('products')->find($product->id),
                ]);
            }

            return response()->json([
                'message' => 'Nothing has changed',
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
    public function destroy(Request $request)
    {
        try {
            
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'exists:products,id',
            ]);

            Gate::authorize('delete', $request);

            Product::whereIn('id', $request->product_ids)->delete();

            return response()->json([
                'message' => 'Products deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
