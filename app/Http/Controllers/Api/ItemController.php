<?php

namespace App\Http\Controllers\Api;

use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Varient;
use App\Http\Resources\ItemResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Cart $cart, Request $request)
    {
        if (!Gate::allows('viewAny', CartItem::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1',
            ]);

            $perPage = $validated['per_page'] ?? 10;

            $cartItems = CartItem::where('cart_id', $cart->id)
                ->with([
                    'varient',
                    'varient.product',
                    'varient.product.brand',
                    'varient.product.category',
                ])
                ->paginate($perPage);

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'No items found for the given cart.',
                ], 404);
            }

            return response()->json([
                'cart_items' => ItemResource::collection($cartItems->items()),
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
    public function update(Request $request, CartItem $cartItem)
    {
        try {
            // Authorize user before updating
            Gate::authorize('update', $cartItem);

            // Validate request data
            $validated = $request->validate([
                'varient_id' => 'nullable|exists:varients,id',
                'quantity' => 'nullable|integer|min:1',
            ]);

            // Ensure cart item has a valid variant before checking product_id
            if (!$cartItem->varient) {
                return response()->json([
                    'message' => 'The cart item does not have a valid variant.',
                ], 400);
            }

            // If changing the variant, ensure it's from the same product
            if (isset($validated['varient_id']) && $validated['varient_id'] != $cartItem->varient_id) {
                $newVariant = Varient::find($validated['varient_id']);

                if (!$newVariant || $newVariant->product_id !== $cartItem->varient->product_id) {
                    return response()->json([
                        'message' => 'The selected variant does not belong to the same product.',
                    ], 400);
                }

                // Update variant_id and price
                $cartItem->varient_id = $validated['varient_id'];
                $cartItem->price = $newVariant->price;
            }

            // Update quantity if provided
            if (isset($validated['quantity'])) {
                $cartItem->quantity = $validated['quantity'];
            }

            // Update total price
            $cartItem->total_price = $cartItem->price * $cartItem->quantity;
            $cartItem->save();

            return response()->json([
                'message' => 'Cart item updated successfully.',
                'cart_item' => new ItemResource($cartItem),
            ], 200);
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
    public function destroy($cart_id, $id)
    {
        $cartItem = CartItem::where('id', $id)
            ->where('cart_id', $cart_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found for the given cart_id and id.',
            ], 404);
        }
        Gate::authorize('delete', $cartItem);

        try {
            $cartItem->delete();

            return response()->json([
                'message' => 'Cart item deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
