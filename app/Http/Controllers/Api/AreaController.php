<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use Illuminate\Support\Facades\Cache;
use App\Models\City;
use Illuminate\Support\Facades\Gate;
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
            // Authorize before retrieving the areas
            if (!Gate::allows('viewAny', Area::class)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Define a cache key based on the pagination and request parameters
            $cacheKey = 'areas_' . $request->query('per_page', 10);

            // Attempt to retrieve the areas from cache or run the query if not cached
            $areas = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {
                // Paginate the areas and eager load their related cities
                return Area::with('city')->paginate($request->query('per_page', 10));
            });

            // Return the paginated resource collection
            return AreaResource::collection($areas);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
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

            if (!Gate::allows('create', Area::class)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

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
            // Cache key for fetching a single area by ID
            $cacheKey = "area_{$id}";

            // Retrieve the area from the cache or from the database if not cached
            $area = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($id) {
                return Area::with('city')->find($id);
            });

            // Check if the area exists
            if (!$area) {
                return response()->json(['message' => 'Area not found'], 404);
            }

            // Authorization check
            if (!Gate::allows('view', $area)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Return the area data as a resource
            return new AreaResource($area);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showByName($areaName)
    {
        try {
            // Cache key for searching areas by name
            $cacheKey = "areas_by_name_{$areaName}";

            // Attempt to get the areas from cache or run the query if not cached
            $areas = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($areaName) {
                return Area::with('city')
                ->where('Area_name', 'LIKE', "%$areaName%")
                ->paginate(10); // Paginate the result set
            });

            // If no areas are found, return a 404 response
            if ($areas->isEmpty()) {
                return response()->json(['message' => 'No matching areas found'], 404);
            }

            // Authorization check for viewing areas
            if (!Gate::allows('viewAny', Area::class)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Return the paginated results as a resource collection
            return AreaResource::collection($areas);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
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

            if (!$request->user()->can('update', $area)) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

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
    public function destroy(Request $request, $areaId)
    {
        if (!$request->user()->can('delete', $areaId)) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $area = Area::with('orderItems')->find($areaId);

        // Check if the order exists
        if (!$area) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

    $area->delete();


    return response()->json([
        'Message' => 'Deleted successfully'
    ]);
    }
}
