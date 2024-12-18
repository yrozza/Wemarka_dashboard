<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CityResource::collection(City::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'City_name' => 'required|string|max:255|unique:cities,City_name',
                'Active' => 'required|boolean'
            ]);

            $city = new City();
            $city->City_name = $validated['City_name'];
            $city->Active = $validated['Active'];
            $city->save();

            return response()->json([
                'Message' => 'Created Sucessfully',
                'City' => $city 
            ]); 
        
        } catch (\Exception $e) {
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
        $city = City::find($id);
        try {
            if(!$city){
                return response()->json([
                    'Message' => 'City not found'
                ],404);
            }

            return new CityResource($city);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showByName($city_name){
        $city = City::where('City_name','LIKE',"%$city_name%")->get();

        try {
            if($city->isEmpty()){
                return response()->json([
                    'Message' => 'City not found'
                ],404);
            }
            return CityResource::collection($city);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city)
    {
        try {
            // Validate the input data
            $validated = $request->validate([
                'City_name' => 'required|string|max:255|unique:cities,City_name,' . $city->id,
                'Active' => 'required|boolean'
            ]);

            // Update the city record with the validated data
            $city->update($validated);

            // Check if the city was changed
            if (!$city->wasChanged()) {
                // If no changes were made, return a message
                return response()->json([
                    'message' => 'Nothing has changed'
                ]);
            }
            $city->makeHidden(['created_at', 'updated_at']);

            // If there were changes, return the success message with updated city data
            return response()->json([
                'message' => 'Updated successfully',
                'city' => $city
            ]);
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
    public function destroy(City $city)
    {
        $city->delete($city);

        return response()->json([
            'Message' => 'deleted sucessfully'
        ]);
    }
}
