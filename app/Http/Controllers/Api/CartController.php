<?php

namespace App\Http\Controllers\Api;
use App\Models\Cart;
Use App\Http\Resources\CartResource;
use App\Models\Varient;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Define the number of items per page (can be adjusted or made dynamic via query parameter)
        $perPage = $request->input('per_page', 10); // Default to 10 per page if not provided

        // Retrieve paginated carts from the database, eager load the client relationship
        $carts = Cart::with('client')->paginate($perPage);

        // Return paginated carts with the CartResource formatting
        return response()->json([
            'message' => 'Carts retrieved successfully.',
            'carts' => CartResource::collection($carts),
            'pagination' => [
                'total' => $carts->total(),
                'current_page' => $carts->currentPage(),
                'per_page' => $carts->perPage(),
                'last_page' => $carts->lastPage(),
                'from' => $carts->firstItem(),
                'to' => $carts->lastItem(),
            ]
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'products' => 'required|array',
                'products.*.varient_id' => 'required|exists:varients,id',  // Ensure variant exists
                'products.*.quantity' => 'nullable|integer|min:1',
            ]);

            // Find a cart that belongs to the client, where status is 'active', 'checkout', or 'abandoned'
            $cart = Cart::where('client_id', $validated['client_id'])
            ->whereIn('status', ['active', 'checkout', 'abandoned'])
            ->first();

            if ($cart) {
                // If the cart status is 'checkout' or 'abandoned', update its status to 'active'
                if (in_array($cart->status, ['checkout', 'abandoned'])) {
                    $cart->update(['status' => 'active']);
                }
            } else {
                // If no such cart exists, create a new cart with 'active' status
                $cart = Cart::create([
                    'client_id' => $validated['client_id'],
                    'status' => 'active', // Default status
                ]);
            }

            // Loop through each product in the products array
            foreach ($validated['products'] as $product) {
                // Retrieve the variant from the database using variant_id
                $variant = Varient::findOrFail($product['varient_id']);

                // Calculate the total price for the item
                $totalPrice = $variant->price * ($product['quantity'] ?? 1);

                // Check if the variant is already in the cart
                $cartItem = $cart->cartItems()->where('varient_id', $product['varient_id'])->first();

                if ($cartItem) {
                    // If the variant is already in the cart, update the quantity and total price
                    $cartItem->quantity += $product['quantity'] ?? 1;
                    $cartItem->total_price = $cartItem->quantity * $variant->price;
                    $cartItem->save();
                } else {
                    // If the variant is not in the cart, create a new cart item
                    $cart->cartItems()->create([
                        'varient_id' => $product['varient_id'],
                        'quantity' => $product['quantity'] ?? 1,
                        'price' => $variant->price,  // Set the price from the variant
                        'total_price' => $totalPrice, // Set the total price for the cart item
                    ]);
                }
            }

            // Load the cart items and return them in the response along with the cart
            $cart->load('cartItems');  // Eager load the cartItems relationship

            return response()->json([
                'message' => 'Products added to cart successfully.',
                'cart' => new CartResource($cart), // Return the updated cart resource
                'cart_items' => $cart->cartItems, // Include the cart items in the response
            ], 201);
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
        // Retrieve the cart by its ID with the associated client (eager loading)
        $cart = Cart::with('client')->find($id);

        // Check if the cart exists
        if (!$cart) {
            return response()->json(['message' => 'Cart nooot found.'], 404);
        }

        // Return the cart using the CartResource to format the response
        return new CartResource($cart);
    }

    public function showByName(Request $request)
    {
        try {
            // Get client_name from request
            $clientName = $request->client_name;

            // Check if client_name is provided
            if (!$clientName) {
                return response()->json([
                    'message' => 'Client name is required'
                ], 400);
            }

            // Define pagination parameters (you can customize these)
            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not provided

            // Retrieve paginated carts based on client_name using a JOIN between Cart and Client
            $carts = Cart::join('clients', 'clients.id', '=', 'carts.client_id')
            ->where('clients.client_name', 'like', '%' . $clientName . '%')
                ->select('carts.*') // Select only columns from the 'carts' table
                ->with('client') // Eager load client relationship
                ->paginate($perPage); // Paginate the results

            // If no carts found
            if ($carts->isEmpty()) {
                return response()->json([
                    'message' => 'Cart not found.'
                ], 404);
            }

            // Return the paginated carts with pagination details
            return response()->json([
                'message' => 'Carts retrieved successfully.',
                'carts' => CartResource::collection($carts),
                'pagination' => [
                    'total' => $carts->total(),
                    'current_page' => $carts->currentPage(),
                    'per_page' => $carts->perPage(),
                    'last_page' => $carts->lastPage(),
                    'from' => $carts->firstItem(),
                    'to' => $carts->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            // Catch any error and return the 500 internal server error
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Find the cart by its ID
            $cart = Cart::find($id);

            // If the cart does not exist, return a 404 error
            if (!$cart) {
                return response()->json([
                    'message' => 'Cart not found.'
                ], 404);
            }

            // Validate the incoming request data with custom error message
            $validated = $request->validate([
                'status' => 'nullable|in:active,checked_out,abandoned', // Allow null or specific values
            ], [
                'status.in' => 'Sorry, invalid status.', // Custom error message for invalid status
            ]);

            // Ensure client_id is not in the validated data (for safety)
            if (array_key_exists('client_id', $validated)) {
                unset($validated['client_id']);
            }

            // Check if there are changes before updating
            $isChanged = false;

            foreach ($validated as $key => $value) {
                // Allow null values in the comparison
                if (($value === null && $cart->$key !== null) || ($value !== null && $cart->$key !== $value)) {
                    $isChanged = true;
                    break; // Exit loop once a change is found
                }
            }

            if ($isChanged) {
                // Use mass assignment to update the validated fields
                $cart->update($validated);

                // Return the updated cart resource
                return new CartResource($cart);
            } else {
                // Return response indicating no changes
                return response()->json([
                    'message' => 'No changes have been made to the cart.'
                ], 200);
            }
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 error
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the cart by its ID
            $cart = Cart::find($id);

            // If the cart does not exist, return a 404 error
            if (!$cart) {
                return response()->json([
                    'message' => 'Cart not found.'
                ], 404);
            }

            // Delete the cart
            $cart->delete();

            // Return a success message
            return response()->json([
                'message' => 'Cart deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 error
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
