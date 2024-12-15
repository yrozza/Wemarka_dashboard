<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource; // Fix this line
use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SourceResource::collection(Source::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'Source_name' => 'required|unique:sources,Source_name|max:255',
                'Active' => 'required|in:0,1',  // Now checking for 0 or 1
            ]);

            // Convert the string value of Active to a boolean (true/false)
            $isActive = $validated['Active'] == 1;  // Now using 1 instead of 'active'

            // Create a new source record if validation passes
            $source = new Source;
            $source->Source_name = $validated['Source_name'];
            $source->Active = $isActive; // Save as 0 or 1
            $source->save();

            // Return a response indicating success
            return response()->json([
                'message' => 'Source created successfully!',
                'source' => $source
            ], 201);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



///////////////////////////////////////////////////////////////////////////////////////

    /**
     * Display the specified resource.
     */
    public function show(string $Source_name)
    {
        // Find sources where 'Source_name' contains the provided letter or substring (case-insensitive)
        $sources = Source::where('Source_name', 'LIKE', "%$Source_name%")->get();

        // If no sources are found, return an error message
        if ($sources->isEmpty()) {
            return response()->json(['message' => 'No sources found'], 404);
        }

        // Map through the sources to check if they're active or not and return the data
        $sources_data = $sources->map(function ($source) {
            return [
                'Source_name' => $source->Source_name,
                'status' => $source->Active ? 'active' : 'not active',
            ];
        });

        // Return the sources data with status
        return response()->json($sources_data);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Source $source)
    {
        $source->delete();

        return response()->json([
            'message'=> 'Deleted successfully'
        ]);
    }
}
