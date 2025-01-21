<?php

namespace App\Http\Controllers\Api;

use App\Models\CartItem;
use App\Http\Resources\ItemResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'cart_id' => 'required|exists:carts,id',  // Ensure cart_id exists in the carts table
                'per_page' => 'nullable|integer|min:1',  // Optional per_page query parameter to control page size
            ]);

            // Get the 'per_page' value from the request or default to 10 if not provided
            $perPage = $request->get('per_page', 10);

            // Retrieve the cart items for the given cart_id with pagination
            $cartItems = CartItem::where('cart_id', $validated['cart_id'])
            ->with([
                'varient',
                'varient.product',
                'varient.product.brand',
                'varient.product.category'
            ])
                ->paginate($perPage); // Apply pagination

            // If no cart items found, return a 404 response
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'No items found for the given cart.',
                ], 404);
            }

            // Return paginated cart items as a JSON response
            return response()->json([
                'cart_items' => ItemResource::collection($cartItems),
                'pagination' => [
                    'total' => $cartItems->total(),
                    'per_page' => $cartItems->perPage(),
                    'current_page' => $cartItems->currentPage(),
                    'last_page' => $cartItems->lastPage(),
                    'next_page_url' => $cartItems->nextPageUrl(),
                    'prev_page_url' => $cartItems->previousPageUrl(),
                ]
            ], 200);
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
