<?php

namespace App\Http\Controllers\Api;

use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Package;
use App\Models\Varient;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ItemResource;
use App\Http\Controllers\Controller;
use App\Models\PackageVarient;
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

            // Unique cache key based on cart ID and pagination
            $cacheKey = "cart_items:{$cart->id}:page_{$request->get('page', 1)}:per_page_{$perPage}";

            $cartItems = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($cart, $perPage) {
                return CartItem::where('cart_id', $cart->id)
                    ->with([
                        'varient',
                        'varient.product',
                        'varient.product.brand',
                        'varient.product.category',
                    ])
                    ->paginate($perPage);
            });

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
            // Prevent updates if the item belongs to a package
            if ($cartItem->package_id !== null) {
                return response()->json([
                    'message' => 'This item is part of a package and cannot be updated individually.',
                ], 403);
            }

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


    public function updatePackageVariant(Request $request, $cart_id, CartItem $cartItem)
    {
        try {
            // Ensure the cart item belongs to the given cart_id
            if ($cartItem->cart_id != $cart_id) {
                return response()->json(['message' => 'Invalid cart item for the given cart.'], 403);
            }

            // Ensure the cart item is a package (required)
            if (!$cartItem->package_id) {
                return response()->json(['message' => 'This item is not a package.'], 400);
            }

            // Authorize the user before updating
            Gate::authorize('update', $cartItem);

            // Validate the request (only variant_id allowed)
            $validated = $request->validate([
                'varient_id' => 'required|exists:varients,id', // Required for variant change
            ]);

            // Find the new variant
            $newVariant = Varient::find($validated['varient_id']);

            if (!$newVariant) {
                return response()->json(['message' => 'The selected variant does not exist.'], 400);
            }

            // Ensure the new variant belongs to the same product as the current variant
            $currentProduct = $cartItem->varient->product_id ?? null;

            if (!$currentProduct || $newVariant->product_id !== $currentProduct) {
                return response()->json(['message' => 'The selected variant does not belong to the same product.'], 400);
            }

            // Update only the variant (Package remains unchanged)
            $cartItem->varient_id = $validated['varient_id'];
            $cartItem->price = $newVariant->price;

            // Save changes
            $cartItem->save();

            return response()->json([
                'message' => 'Variant inside package updated successfully.',
                'cart_item' => new ItemResource($cartItem),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }







    public function updateCartPackage(Request $request, $cart_id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'package_id' => 'required|exists:packages,id', // The package to update
                'new_package_id' => 'required|exists:packages,id', // The new package to set
            ]);

            // Find all cart items with the specified package
            $cartItems = CartItem::where('cart_id', $cart_id)
                ->where('package_id', $validated['package_id'])
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'The specified package does not exist in the cart.'], 404);
            }

            // Authorize the user before updating (Check first item)
            Gate::authorize('update', $cartItems->first());

            // Fetch the new package with its variants
            $newPackage = Package::with('varients')->find($validated['new_package_id']);

            if (!$newPackage) {
                return response()->json(['message' => 'The selected new package does not exist.'], 400);
            }

            // Delete old cart items linked to the package
            foreach ($cartItems as $cartItem) {
                $cartItem->delete();
            }

            // Add new package items to the cart
            foreach ($newPackage->varients as $variant) {
                CartItem::create([
                    'cart_id' => $cart_id,
                    'package_id' => $validated['new_package_id'],
                    'varient_id' => $variant->id,
                    'price' => $newPackage->price,
                    'quantity' => 1,
                ]);
            }

            return response()->json([
                'message' => 'Package updated successfully.',
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

        // Prevent deletion if the item is part of a package
        if ($cartItem->package_id !== null) {
            return response()->json([
                'message' => 'This item is part of a package and cannot be deleted individually.',
            ], 403);
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
