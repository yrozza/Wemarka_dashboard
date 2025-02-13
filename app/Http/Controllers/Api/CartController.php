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
use Illuminate\Support\Facades\Gate;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Cart::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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
            if (Gate::denies('create', Cart::class)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
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

        if (!Gate::allows('view', $cart)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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
            if (!Gate::allows('viewAny', Cart::class)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
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


    public function update(Request $request, string $id)
    {
        try {
            // Find the cart by its ID
            $cart = Cart::find($id);

            if (!$cart) {
                return response()->json([
                    'message' => 'Cart not found.'
                ], 404);
            }

            //✅ Use Gate::allows() instead of Gate::authorize()
            if (!Gate::allows('update', $cart)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Validate the request
            $validated = $request->validate([
                'status' => 'nullable|in:active,checked_out,abandoned',
            ], [
                'status.in' => 'Sorry, invalid status.',
            ]);

            // Prevent updating `client_id`
            unset($validated['client_id']);

            // Check if there are changes
            if (!$validated || $cart->only(array_keys($validated)) == $validated) {
                return response()->json([
                    'message' => 'No changes have been made to the cart.'
                ], 200);
            }

            // Update cart
            $cart->update($validated);

            return new CartResource($cart);
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

            Gate::authorize('delete', $cart);
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
                'city_id' => 'required|exists:cities,id',
                'area_id' => 'required|exists:areas,id',
                'Address' => 'required|string|max:255',
                'client_notes' => 'nullable|string|max:255',
                'additional_phone' => 'nullable|string|max:20',
                'Cost_shipping_price' => 'nullable|numeric',
                'Shipping_price' => 'required|numeric',
                'packing' => 'nullable|boolean',
                'packing_price' => 'nullable|numeric|gte:0',
                'is_discount' => 'nullable|boolean',
                'discount_value' => 'nullable|numeric|between:0,100'
            ]);

            // Find the cart
            $cart = Cart::findOrFail($cartId);
            if (!$request->user()->can('checkout', Cart::class)) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            // Ensure the cart has items
            if ($cart->cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty.'], 400);
            }

            // Retrieve Area and City
            $area = Area::findOrFail($validated['area_id']);
            $city = City::findOrFail($validated['city_id']);

            // Ensure the area belongs to the selected city
            if ($area->city_id !== $city->id) {
                return response()->json(['message' => 'The area does not belong to the selected city.'], 400);
            }

            // Retrieve client information
            $client = Client::findOrFail($cart->client_id);

            DB::beginTransaction();

            // Calculate the total price (products only)
            $totalPrice = $cart->cartItems->sum(fn($item) => $item->price * $item->quantity);

            // Add shipping and packing costs
            $costShippingPrice = $validated['Cost_shipping_price'] ?? 0;
            $totalPrice += $costShippingPrice + $validated['Shipping_price'];

            if ($request->has('packing') && $validated['packing'] && $validated['packing_price'] > 0) {
                $totalPrice += $validated['packing_price'];
            }

            // Apply discount
            $discount = 0;
            if ($validated['is_discount'] && isset($validated['discount_value'])) {
                $discount = ($validated['discount_value'] / 100) * $totalPrice;
                $totalPrice -= $discount;
            }

            // Create the order
            $order = Order::create([
                'client_id' => $cart->client_id,
                'cart_id' => $cart->id,
                'status' => 'pending',
                'total_price' => $totalPrice,
                'shipping_status' => 'not_shipped',
                'city_id' => $validated['city_id'],
                'area_id' => $validated['area_id'],
                'Address' => $validated['Address'],
                'client_notes' => $validated['client_notes'] ?? null,
                'area_name' => $area->Area_name,
                'city_name' => $city->City_name,
                'client_name' => $client->client_name,
                'client_phone' => $client->client_phonenumber,
                'additional_phone' => $validated['additional_phone'] ?? null,
                'Cost_shipping_price' => $costShippingPrice,
                'Shipping_price' => $validated['Shipping_price'],
                'packing' => $validated['packing'] ?? false,
                'packing_price' => $validated['packing_price'] ?? null,
                'is_discount' => $validated['is_discount'] ?? false,
                'discount' => $discount,
            ]);

            // Create order items and update stock
            foreach ($cart->cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'varient_id' => $cartItem->varient_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                ]);

                $variant = Varient::find($cartItem->varient_id);
                if ($variant) {
                    if ($variant->stock < $cartItem->quantity) {
                        throw new \Exception("Insufficient stock for variant ID: {$cartItem->varient_id}");
                    }

                    $variant->stock -= $cartItem->quantity;
                    $variant->Stock_status = $variant->stock == 0 ? 'out_of_stock' : ($variant->stock < 10 ? 'Almost_finished' : 'in_stock');
                    $variant->save();
                }
            }

            // Update cart status
            $cart->update(['status' => 'checked_out']);

            DB::commit();

            return response()->json([
                'message' => 'Checkout successful!',
                'order' => $order,
            ], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred during checkout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }












}


