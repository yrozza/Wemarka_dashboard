<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use App\Models\Area;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   

    public function index()
    {
        try {
            // Get all areas with their related cities
            $areas = Area::with('city')->get();

            return response()->json([
                'areas' => $areas->map(function ($area) {
                    return [
                        'id' => $area->id,
                        'name' => $area->Area_name,
                        'shipping_price' => $area->Price,
                        'Active' => $area->Active ? 'Active' : 'Not active',
                        'city_name' => $area->city->City_name ?? 'N/A', // Display city name or 'N/A' if no city found
                    ];
                }),
            ]);
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
        try {
            // Validate incoming request
            $validated = $request->validate([
                'Area_name' => 'required|string|unique:areas,Area_name|max:255',
                'price' => 'required|numeric',
                'active' => 'required|boolean',
                'city_name' => 'required|string|exists:cities,City_name', // Check if the city name exists in cities table
            ]);

            // Find the city by name
            $city = City::where('City_name', $validated['city_name'])->first();

            // Create a new Area with the found city_id
            $area = new Area();
            $area->Area_name = $validated['Area_name'];
            $area->price = $validated['price'];
            $area->active = $validated['active'];
            $area->city_id = $city->id; // Assign the city_id based on the city name
            $area->save();

            return response()->json([
                'message' => 'Area created successfully!',
                'Area' => $area
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
        try {
            $Area = Area::with('city')->find($id);
        if(!$Area){
            return response()->json([
                'Message' => 'Area not found'
            ],404);
        }
            return [
                'id' => $Area->id,
                'name' => $Area->Area_name,
                'shipping_price' => $Area->Price,
                'Active' => $Area->Acive ? 'Active' : 'Not active',
                'city_name' => $Area->city->City_name ?? 'N/A', // Display city name or 'N/A' if no city found
            ];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showByName($Area)
    {
        try {
            // Use get() to retrieve a collection of Areas
            $areas = Area::with('city')->where('Area_name', 'LIKE', "%$Area%")->get();

            // If no areas are found
            if ($areas->isEmpty()) {
                return response()->json([
                    'Message' => 'Area not found'
                ], 404);
            }

            // Loop through each area in the collection and return the data
            $result = $areas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->Area_name,
                    'shipping_price' => $area->Price,
                    'Active' => $area->Active ? 'Active' : 'Not active',
                    'city_name' => $area->city->City_name ?? 'N/A', // Display city name or 'N/A' if no city found
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'Area_name' => 'required|string|max:255|unique:areas,Area_name,' . $id,
                'price' => 'required|numeric',
                'active' => 'required|boolean',
                'city_name' => 'required|string|exists:cities,City_name',
            ]);

            // Find the area by ID
            $area = Area::find($id);

            // Check if the area exists
            if (!$area) {
                return response()->json(['message' => 'Area not found'], 404);
            }

            // Find the city by name
            $city = City::where('City_name', $validated['city_name'])->first();

            // Update the area
            $area->Area_name = $validated['Area_name'];
            $area->price = $validated['price'];
            $area->active = $validated['active'];
            $area->city_id = $city->id; // Update the city_id based on city name
            $area->save();

            // Return the updated area
            return response()->json([
                'message' => 'Area updated successfully!',
                'Area' => $area
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function updateOnlyOne(Request $request, $id)
    // {
    //     try {
    //         // Find the Area by ID
    //         $area = Area::find($id);
    //         if (!$area) {
    //             return response()->json([
    //                 'Message' => 'Not found'
    //             ], 404);
    //         }

    //         // Check if the request has any data
    //         if (!$request->all()) {
    //             return response()->json([
    //                 'Message' => 'No input provided'
    //             ], 400);
    //         }

    //         // Validate the input
    //         $validated = $request->validate([
    //             'Area_name' => 'sometimes|string|max:255|unique:areas,Area_name,' . $id,
    //             'price' => 'sometimes|numeric',
    //             'active' => 'sometimes|boolean',
    //             'city_name' => 'sometimes|string|exists:cities,City_name',  // Ensures the city_name exists in the cities table
    //         ]);

    //         // If city_name is provided, update the foreign key relationship
    //         if (isset($validated['city_name'])) {
    //             // Retrieve the City ID based on the provided city_name
    //             $city = City::where('City_name', $validated['city_name'])->first();
    //             if ($city) {
    //                 $area->city_id = $city->id; // Assuming you have a `city_id` column in `Area` table
    //             } else {
    //                 return response()->json([
    //                     'Message' => 'Invalid city_name provided'
    //                 ], 400);
    //             }
    //         }

    //         // Update the record
    //         $area->update($validated);

    //         // Check if changes were actually made
    //         if (!$area->wasChanged()) {
    //             return response()->json([
    //                 'Message' => 'No changes were detected'
    //             ], 400);
    //         }

    //         return response()->json([
    //             'Message' => 'Updated successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Internal Server Error',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
    $area->delete();

    return response()->json([
        'Message' => 'Deleted successfully'
    ]);
    }
}
