<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\City;
use Illuminate\Http\Request;
use App\Models\Area;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Get paginated areas with their related cities
            $perPage = $request->query('per_page', 10); // Default 10 per page
            $areas = Area::with('city')->paginate($perPage);

            // Return paginated resource collection
            return AreaResource::collection($areas);
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
                'city_id' => 'required|integer', // Ensure city_id is an integer
            ]);

            // Check if the city exists
            $city = City::find($validated['city_id']);
            if (!$city) {
                return response()->json([
                    'message' => 'City not found'
                ], 404);
            }

            // Create a new Area record
            $area = Area::create([
                'Area_name' => $validated['Area_name'],
                'Price' => $validated['price'],
                'active' => $validated['active'],
                'city_id' => $validated['city_id'],
            ]);

            return response()->json([
                'message' => 'Area created successfully!',
                'Area' => new AreaResource($area) // Return structured response
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
            $area = Area::with('city')->find($id);

            if (!$area) {
                return response()->json([
                    'message' => 'Area not found'
                ], 404);
            }

            return new AreaResource($area); // Return as a resource

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showByName($areaName)
    {
        try {
            // Use pagination with LIKE search
            $areas = Area::with('city')
            ->where('Area_name', 'LIKE', "%$areaName%")
            ->paginate(10); // Paginate with 10 results per page

            // If no areas are found, return a 404 response
            if ($areas->isEmpty()) {
                return response()->json([
                    'message' => 'No matching areas found'
                ], 404);
            }

            // Return paginated resource response
            return AreaResource::collection($areas);
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
            // Validate the request
            $validated = $request->validate([
                'Area_name' => 'sometimes|string|max:255|unique:areas,Area_name,' . $id,
                'price' => 'sometimes|numeric',
                'active' => 'sometimes|boolean',
                'city_id' => 'sometimes|exists:cities,id',
            ]);

            // Find the area
            $area = Area::findOrFail($id);

            // Update the area with only provided values
            $area->update($validated);

            // Return updated area with resource
            return new AreaResource($area);
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
    public function destroy(Area $area)
    {
    $area->delete();

    return response()->json([
        'Message' => 'Deleted successfully'
    ]);
    }
}
