<?php

namespace App\Http\Controllers\Api;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
Use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Area;
use App\Models\City;
use App\Models\OrderItem;
use App\Models\client;
use App\Models\Package;
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
        try {
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
    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'packages' => 'nullable|array',
                'packages.*.package_id' => 'required|exists:packages,id',
                'packages.*.quantity' => 'required|integer|min:1',
                'variants' => 'nullable|array',
                'variants.*.varient_id' => 'required|exists:varients,id',
                'variants.*.quantity' => 'required|integer|min:1',
            ]);

            Gate::authorize('create', Cart::class);

            // Get or create cart for the client
            DB::table('carts')->updateOrInsert(
                ['client_id' => $validated['client_id'], 'status' => 'active'],
                ['updated_at' => now()]
            );

            // Retrieve the cart ID
            $cart = DB::table('carts')
            ->where('client_id', $validated['client_id'])
            ->where('status', 'active')
            ->first();

            if (!$cart) {
                return response()->json(['message' => 'Cart not found or could not be created.'], 500);
            }

            // Handle package additions
            if (!empty($validated['packages'])) {
                foreach ($validated['packages'] as $packageData) {
                    $package = DB::table('packages')->where('id', $packageData['package_id'])->first();

                    if (!$package) {
                        return response()->json(['message' => 'Package not found.'], 404);
                    }

                    // Fetch package products
                    $packageProducts = DB::table('package_product')
                    ->where('package_id', $packageData['package_id'])
                    ->get();

                    foreach ($packageProducts as $packageProduct) {
                        $varient = DB::table('varients')->where('id', $packageProduct->varient_id)->first();

                        if (!$varient || $varient->stock < ($packageProduct->quantity * $packageData['quantity'])) {
                            return response()->json([
                                'message' => "Package cannot be added. Variant ID {$packageProduct->varient_id} is out of stock.",
                            ], 400);
                        }

                        // Update or insert package item in cart
                        DB::table('cart_items')->updateOrInsert(
                            [
                                'cart_id' => $cart->id,
                                'package_id' => $packageData['package_id'],
                                'varient_id' => $packageProduct->varient_id,
                            ],
                            [
                                'quantity' => DB::raw("quantity + " . ($packageProduct->quantity * $packageData['quantity'])),
                                'price' => $varient->price,
                                'total_price' => DB::raw("total_price + " . ($varient->price * ($packageProduct->quantity * $packageData['quantity']))),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }

            // Handle variant additions
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $variantData) {
                    $varient = DB::table('varients')->where('id', $variantData['varient_id'])->first();

                    if (!$varient || $varient->stock < $variantData['quantity']) {
                        return response()->json(['message' => "Variant ID {$variantData['varient_id']} is out of stock."], 400);
                    }

                    DB::table('cart_items')->updateOrInsert(
                        [
                            'cart_id' => $cart->id,
                            'varient_id' => $variantData['varient_id'],
                        ],
                        [
                            'quantity' => DB::raw("quantity + " . $variantData['quantity']),
                            'price' => $varient->price,
                            'total_price' => DB::raw("total_price + " . ($varient->price * $variantData['quantity'])),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            return response()->json(['message' => 'Cart Added successfully.'], 200);
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

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        // Retrieve the active cart
        $cart = Cart::where('client_id', $validated['client_id'])
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'No active cart found.'], 404);
        }

        DB::beginTransaction();
        try {
            // Create new order
            $order = Order::create([
                'client_id' => $cart->client_id,
                'status' => 'pending',
                'total_price' => 0,
            ]);

            $totalPrice = 0;

            // Process cart items
            $cartItems = DB::table('cart_items')->where('cart_id', $cart->id)->get();
            foreach ($cartItems as $cartItem) {
                if ($cartItem->package_id) {
                    // Handle package checkout
                    $package = Package::find($cartItem->package_id);
                    if (!$package) {
                        return response()->json(['message' => 'Package not found.'], 404);
                    }

                    $packageProducts = DB::table('package_product')
                        ->where('package_id', $package->id)
                        ->get();

                    foreach ($packageProducts as $packageProduct) {
                        $variant = Varient::find($packageProduct->product_id);
                        if (!$variant || $variant->stock < $packageProduct->quantity) {
                            return response()->json([
                                'message' => "Package cannot be checked out because a product is out of stock.",
                            ], 400);
                        }
                    }

                    // Deduct stock for package products
                    foreach ($packageProducts as $packageProduct) {
                        $variant = Varient::find($packageProduct->product_id);
                        $variant->decrement('stock', $packageProduct->quantity);

                        // Add each package product to order
                        OrderItem::create([
                            'order_id' => $order->id,
                            'varient_id' => $variant->id,
                            'quantity' => $packageProduct->quantity,
                            'price' => $variant->price,
                            'total_price' => $variant->price * $packageProduct->quantity,
                            'package_id' => $package->id, // Linking to package
                        ]);

                        $totalPrice += $variant->price * $packageProduct->quantity;
                    }
                } else {
                    // Handle individual product checkout
                    $variant = Varient::find($cartItem->varient_id);
                    if (!$variant || $variant->stock < $cartItem->quantity) {
                        return response()->json([
                            'message' => "Not enough stock for variant ID {$cartItem->varient_id}.",
                        ], 400);
                    }

                    // Deduct stock
                    $variant->decrement('stock', $cartItem->quantity);

                    // Add item to order
                    OrderItem::create([
                        'order_id' => $order->id,
                        'varient_id' => $cartItem->varient_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $variant->price,
                        'total_price' => $variant->price * $cartItem->quantity,
                    ]);

                    $totalPrice += $variant->price * $cartItem->quantity;
                }
            }

            // Update order total price
            $order->update(['total_price' => $totalPrice]);

            // Mark cart as checked out
            $cart->update(['status' => 'checked_out']);

            DB::commit();

            return response()->json([
                'message' => 'Checkout successful.',
                'order_id' => $order->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed.', 'error' => $e->getMessage()], 500);
        }
    }













}


