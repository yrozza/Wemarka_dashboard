<?php

namespace App\Http\Controllers\Api;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
Use App\Http\Resources\CartResource;
use App\Models\Order;
use App\Models\Area;
use App\Models\City;
use App\Models\OrderItem;
use App\Models\client;
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
                'client_id' => 'required|exists:clients,id', // Ensure the client exists
                'products' => 'required|array',
                'products.*.varient_id' => 'required|exists:varients,id', // Ensure variant exists
                'products.*.quantity' => 'nullable|integer|min:1',
            ]);

            // Check if the client exists in the clients table
            $client = DB::table('clients')->where('id', $validated['client_id'])->first();

            if (!$client) {
                return response()->json([
                    'message' => 'Client not found.',
                ], 404);
            }

            // Check if the client has an active cart
            $cart = DB::table('carts')
            ->where('client_id', $validated['client_id'])
            ->where('status', 'active')
            ->first();

            // If no active cart exists, check for a checked_out cart
            if (!$cart) {
                $checkedOutCart = DB::table('carts')
                ->where('client_id', $validated['client_id'])
                ->where('status', 'checked_out')
                ->first();

                if ($checkedOutCart) {
                    // If a checked_out cart exists, create a new cart for the client
                    $cartId = DB::table('carts')->insertGetId([
                        'client_id' => $validated['client_id'],
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $cart = (object) ['id' => $cartId];
                }
            }

            // If no cart exists, create a new active cart
            if (!$cart) {
                $cartId = DB::table('carts')->insertGetId([
                    'client_id' => $validated['client_id'],
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $cart = (object) ['id' => $cartId];
            }

            // Loop through each product in the request
            foreach ($validated['products'] as $product) {
                $variant = DB::table('varients')->where('id', $product['varient_id'])->first();

                // Check if the variant exists and has enough stock
                if (!$variant || $variant->stock < ($product['quantity'] ?? 1)) {
                    return response()->json([
                        'message' => "Insufficient stock for variant ID: {$product['varient_id']}",
                    ], 400);
                }

                // Check if the variant already exists in the cart
                $cartItem = DB::table('cart_items')
                ->where('cart_id', $cart->id)
                    ->where('varient_id', $product['varient_id'])
                    ->first();

                if ($cartItem) {
                    // Update the quantity and total price if it exists
                    DB::table('cart_items')
                    ->where('id', $cartItem->id)
                        ->update([
                            'quantity' => $cartItem->quantity + ($product['quantity'] ?? 1),
                            'total_price' => ($cartItem->quantity + ($product['quantity'] ?? 1)) * $variant->price,
                            'updated_at' => now(),
                        ]);
                } else {
                    // Add a new cart item if it doesn't exist
                    DB::table('cart_items')->insert([
                        'cart_id' => $cart->id,
                        'varient_id' => $product['varient_id'],
                        'quantity' => $product['quantity'] ?? 1,
                        'price' => $variant->price,
                        'total_price' => $variant->price * ($product['quantity'] ?? 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Fetch the updated cart items
            $cartItems = DB::table('cart_items')->where('cart_id', $cart->id)->get();

            return response()->json([
                'message' => 'Products added to cart successfully.',
                'cart' => $cart,
                'cart_items' => $cartItems,
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

    public function checkout(Request $request, $cartId)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'city_id' => 'required|exists:cities,id', // city_id is required and must exist
                'area_id' => 'required|exists:areas,id', // area_id is required and must exist
                'address' => 'required|string|max:255',  // address is required
                'client_notes' => 'nullable|string|max:255', // Notes are optional
                'additional_phone' => 'nullable|string|max:20', // Optional additional phone number
            ]);

            // Find the cart by the provided cart ID
            $cart = Cart::find($cartId);

            // Check if the cart exists
            if (!$cart) {
                throw new \Exception('Cart not found.');
            }

            // Check if the cart has any items
            if ($cart->cartItems->isEmpty()) {
                throw new \Exception('Cart is empty.');
            }

            // Retrieve Area_name and City_name from the areas and cities tables
            $area = Area::find($validated['area_id']);
            if (!$area) {
                throw new \Exception('Invalid area_id.');
            }

            $city = City::find($validated['city_id']);
            if (!$city) {
                throw new \Exception('Invalid city_id.');
            }

            // Ensure the area belongs to the city
            if ($area->city_id !== $city->id) {
                throw new \Exception('The area does not belong to the selected city.');
            }

            $area_name = $area->Area_name; 
            $city_name = $city->City_name; 

            // Retrieve client details from the clients table
            $client = Client::find($cart->client_id);
            if (!$client) {
                throw new \Exception('Client not found.');
            }

            $client_name = $client->client_name; // Get client_name from the clients table
            $client_phone = $client->client_phonenumber; // Get client_phone from the clients table

            // Begin transaction to ensure atomic operations
            DB::beginTransaction();

            // Calculate the total price
            $totalPrice = $cart->cartItems->sum(function ($cartItem) {
                return $cartItem->price * $cartItem->quantity;
            });

            // Create the order
            $order = Order::create([
                'client_id' => $cart->client_id,
                'cart_id' => $cart->id,
                'status' => 'pending',  // Set order status to pending initially
                'total_price' => $totalPrice,
                'shipping_status' => 'not_shipped',  // Assuming the initial shipping status
                'city_id' => $validated['city_id'], // Save city_id
                'area_id' => $validated['area_id'], // Save area_id
                'address' => $validated['address'], // Save address
                'client_notes' => $validated['client_notes'] ?? null, // Save client_notes if provided, otherwise null
                'area_name' => $area_name, // Save Area_name
                'city_name' => $city_name, // Save City_name
                'client_name' => $client_name, // Save client_name
                'client_phone' => $client_phone, // Save client_phone
                'additional_phone' => $validated['additional_phone'] ?? null, // Save additional phone if provided
            ]);

            // Loop through cart items and create order items
            foreach ($cart->cartItems as $cartItem) {
                // Create the order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'varient_id' => $cartItem->varient_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                ]);

                // Subtract the quantity from the stock in the varients table
                $variant = Varient::find($cartItem->varient_id);

                if ($variant) {
                    // Check if there is enough stock
                    if ($variant->stock < $cartItem->quantity) {
                        throw new \Exception("Insufficient stock for variant ID: {$cartItem->varient_id}");
                    }

                    // Update the stock
                    $variant->stock -= $cartItem->quantity;
                    $variant->save();
                }
            }

            // Update the cart status to checked_out
            DB::table('carts')
                ->where('id', $cartId)
                ->update(['status' => 'checked_out']);

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Checkout successful!', 'order' => $order], 200);
        } catch (\Exception $e) {
            // Rollback transaction if any exception occurs
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred during checkout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }







}


