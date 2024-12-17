<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingResource;
use App\Models\Shipping;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class ShippingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ShippingResource::collection(Shipping::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
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
        if($shipping->isEmpty()){
                return response()->json(['message' => 'No sources found'], 404);
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
            $validatedData = $request->validate([
                'Shipping_name' => 'required|string|max:255|unique:shippings,Shipping_name,' . $shipping->id,  // Exclude current shipping record
                'Active' => 'required|boolean',
                'Address' => 'required|string|max:255',
                'Phonenumber' => 'required|string|max:15|unique:shippings,Phonenumber,' . $shipping->id  // Exclude current shipping record
            ]);

            // Update the shipping record
            $shipping->update($validatedData);

            // Check if any changes were made
            if (!$shipping->wasChanged()) {
                return response()->json([
                    'message' => 'No changes made to the shipping record.',
                ]);
            }

            return response()->json([
                'message' => 'Shipping updated successfully!',
                'data' => $shipping
            ]);
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
    public function destroy(Shipping $shipping)
    {
        $shipping->delete();

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }
}
