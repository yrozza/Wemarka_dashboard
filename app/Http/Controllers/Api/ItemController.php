<?php

namespace App\Http\Controllers\Api;

use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Varient;
use App\Http\Resources\ItemResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Cart $cart, Request $request)
    {
        try {
            // Validate 'per_page' query parameter
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1', // Optional per_page query parameter for pagination
            ]);

            // Set default items per page to 10 if not provided in the request
            $perPage = $validated['per_page'] ?? 10;

            // Retrieve paginated cart items for the given cart with eager loading
            $cartItems = CartItem::where('cart_id', $cart->id)
                ->with([
                    'varient',
                    'varient.product',
                    'varient.product.brand',
                    'varient.product.category',
                ])
                ->paginate($perPage);

            // If no cart items found, return a 404 response
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'No items found for the given cart.',
                ], 404);
            }

            // Return paginated cart items along with pagination details
            return response()->json([
                'cart_items' => ItemResource::collection($cartItems->items()), // Apply the resource to the paginated items
                'pagination' => [
                    'total' => $cartItems->total(),
                    'per_page' => $cartItems->perPage(),
                    'current_page' => $cartItems->currentPage(),
                    'last_page' => $cartItems->lastPage(),
                    'next_page_url' => $cartItems->nextPageUrl(),
                    'prev_page_url' => $cartItems->previousPageUrl(),
                ],
            ], 200);
        } catch (\Exception $e) {
            // Return a 500 error response for unexpected errors
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
    public function update(Request $request, Cart $cart, CartItem $cartItem)
    {
        try {
            // Ensure the cartItem belongs to the specified cart
            if ($cartItem->cart_id !== $cart->id) {
                return response()->json([
                    'message' => 'The cart item does not belong to the specified cart.',
                ], 403);
            }

            // Validate the incoming data
            $validated = $request->validate([
                'varient_id' => 'nullable|exists:varients,id', // Ensure the variant exists
                'quantity' => 'nullable|integer|min:1',       // Ensure quantity is valid
            ]);

            // Check if the user is attempting to update the `variant_id`
            if (isset($validated['varient_id']) && $validated['varient_id'] != $cartItem->varient_id) {
                // Ensure the new variant belongs to the same product (if required)
                $newVariant = Varient::find($validated['varient_id']);
                if (!$newVariant || $newVariant->product_id != $cartItem->varient->product_id) {
                    return response()->json([
                        'message' => 'The selected variant does not belong to the same product.',
                    ], 400);
                }

                // Update the variant_id
                $cartItem->varient_id = $validated['varient_id'];
                $cartItem->price = $newVariant->price; // Update price based on the new variant
            }

            // Update quantity if provided
            if (isset($validated['quantity'])) {
                $cartItem->quantity = $validated['quantity'];
            }

            // Recalculate total price
            $cartItem->total_price = $cartItem->price * $cartItem->quantity;

            // Save the changes
            $cartItem->save();

            // Return the updated cart item as a resource
            return response()->json([
                'message' => 'Cart item updated successfully.',
                'cart_item' => new ItemResource($cartItem),
            ], 200);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($cart_id, $id)
    {
        try {
            // Find the cart item based on the cart_id and id
            $cartItem = CartItem::where('id', $id)
                ->where('cart_id', $cart_id)
                ->first();

            // If the cart item doesn't exist, return a 404 error
            if (!$cartItem) {
                return response()->json([
                    'message' => 'Cart item not found for the giveeen cart_id and id.',
                ], 404);
            }

            // Delete the cart item
            $cartItem->delete();

            // Return a success response
            return response()->json([
                'message' => 'Cart item deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
