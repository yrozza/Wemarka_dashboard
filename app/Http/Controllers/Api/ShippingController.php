<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingResource;
use App\Models\Shipping;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class ShippingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Shipping::class);

        // Paginate results (10 per page by default)
        $shippings = Shipping::paginate(10);

        // Return paginated response with resource
        return ShippingResource::collection($shippings)->response();
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            Gate::authorize('create', Shipping::class);
        $validated = $request->validate([
            'Shipping_name' => 'required|string|unique:shippings,Shipping_name|max:255', 
            'Active' => 'required|boolean', 
            'Address' => 'required|string|max:255', 
            'Phonenumber' => 'required|string|unique:shippings,Phonenumber|max:15', 
        ]);

        $shipping = new Shipping;
        $shipping->Shipping_name=$validated['Shipping_name'];
        $shipping->Active=$validated['Active'];
        $shipping->Address=$validated['Address'];
        $shipping->Phonenumber = $validated['Phonenumber'];
        $shipping->save();


        return response()->json([
            'message' => 'Source created successfully!',
            'shipping' => $shipping
        ], 201);
        }
        catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
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
        $shipping = Shipping::find($id);

        Gate::authorize('view', Shipping::class);

        try{
            if(!$shipping){
                return response()->json([
                    'Message' => 'Not Found'
                ],404);
            }
            return response()->json([
                'data' => $shipping
            ]);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
    }

    
    }

    public function showByName(string $Shipping_name){
        $shipping = Shipping::where('Shipping_name', 'LIKE', "%$Shipping_name%")->get();

    try {
            Gate::authorize('viewAny', Shipping::class);
        if($shipping->isEmpty()){
                return response()->json(['message' => 'No Shipping found'], 404);
        }
    } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
        return response()->json($shipping);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shipping $shipping)
    {
        try {
            Gate::authorize('update', $shipping);
            // Validate only provided fields (supports PATCH)
            $validatedData = $request->validate([
                'Shipping_name' => 'sometimes|required|string|max:255|unique:shippings,Shipping_name,' . $shipping->id,
                'Active' => 'sometimes|required|boolean',
                'Address' => 'sometimes|required|string|max:255',
                'Phonenumber' => 'sometimes|required|string|max:15|unique:shippings,Phonenumber,' . $shipping->id
            ]);

            // Update the shipping record with validated data
            $shipping->update($validatedData);

            // Check if any changes were made
            if (!$shipping->wasChanged()) {
                return response()->json([
                    'message' => 'No changes made to the shipping record.',
                ], 200);
            }

            return new ShippingResource($shipping);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shipping $shipping)
    {
try {
        Gate::authorize('delete', $shipping);

        $shipping->delete();

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
} 

        catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
