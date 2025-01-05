<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VarientResource;
use App\Models\Product;
use App\Models\Varient;
use Illuminate\Http\Request;

class VarientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product)
    {
        $varients = $product->variants()->paginate();

        // Check if there are no variants
        if ($varients->isEmpty()) {
            return response()->json(['message' => 'No variants found for this product.'], 404);
        }

        return VarientResource::collection($varients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
