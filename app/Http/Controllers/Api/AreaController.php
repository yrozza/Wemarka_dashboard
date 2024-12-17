<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\Area;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AreaResource::collection(Area::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated= $request->validate([
            'Area_name' => 'required|string|unique:areas,Area_name|max:255',
            'Active' => 'required|boolean',
            'Price' => 'required|numeric|min:0|max:999999.99',
        ]);

        $area = new Area;
        $area->Area_name = $validated['Area_name'];
        $area->Active=$validated['Active'];
        $area->Price=$validated['Price'];
        $area->save();

        return response()->json([
            'Message' => 'Created Sucessfully',
            'data' => $area
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
        $area = Area::find($id);
        try {
            if(!$area){
                return response()->json([
                    'Message' => 'Area not found'
                ],404);
            }
            return new AreaResource($area);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showByName(string $Area_name)
    {
        try {
            
            $areas = Area::where('Area_name', 'LIKE', "%$Area_name%")->get();

            
            if ($areas->isEmpty()) {
                return response()->json([
                    'Message' => 'Area not found'
                ], 404);
            }

            
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        try {
            $validated = $request->validate([
                'Area_name' => 'required|string|unique:areas,Area_name|max:255',
                'Active' => 'required|boolean',
                'Price' => 'required|numeric|min:0|max:999999.99',
            ]);

            $area->update($validated);
            if (!$area->wasChanged()) {
                return response()->json([
                    'message' => 'No changes made to the shipping record.',
                ]);
            }
            return response()->json([
                'Message' => 'Updated Sucessfully',
                'data' => $area
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
    public function destroy(Area $area)
    {
        $area->delete();

        return response()->json([
            'Message' => 'Deleted sucessfully'
        ]);
    }
}
