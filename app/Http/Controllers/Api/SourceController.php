<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource; // Fix this line
use Illuminate\Support\Facades\Gate;
use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Source::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sources = Source::paginate($request->get('per_page', 10)); // Default to 10 per page

        return SourceResource::collection($sources);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Gate::authorize('create', Source::class);

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
    public function showId($id){
        $source = Source::find($id);

        try {

            if (!Gate::allows('view', $source)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            if (!$id){
                return response()->json([
                'Message' => "Source not found"
                ],404);
            }
            $source_data = [
                'id' => $source->id,
                'Source_name' => $source->Source_name,
                'Active' => $source->Active ? 'Active' : 'Not Active', // 1 -> Active, 0 -> Not Active
            ];
            return response()->json([
                'data' => $source_data
            ]);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show(Request $request, string $Source_name)
    {
        // Authorization check using Gate
        if (!Gate::allows('create', Source::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Find sources where 'Source_name' contains the provided letter or substring (case-insensitive)
        $query = Source::where('Source_name', 'LIKE', "%$Source_name%");

        // Paginate results (default 10 per page, customizable via 'per_page' parameter)
        $sources = $query->paginate($request->get('per_page', 10));

        // If no sources are found, return an error message
        if ($sources->isEmpty()) {
            return response()->json(['message' => 'No sources found'], 404);
        }

        // Map through the paginated sources to check if they're active or not
        $sources->getCollection()->transform(function ($source) {
            return [
                'Source_name' => $source->Source_name,
                'status' => $source->Active ? 'active' : 'not active',
            ];
        });

        // Return the paginated response
        return response()->json($sources);
    }



    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Source $source)
    {
        // Authorization check using policy
        Gate::authorize('update', $source);

        // Validate only provided fields (supports PATCH for partial updates)
        $validated = $request->validate([
            'Source_name' => 'sometimes|required|unique:sources,Source_name,' . $source->id . '|max:255',
            'Active' => 'sometimes|required|in:0,1',
        ]);

        // Update the source with validated data
        $source->update($validated);

        // Return response with resource
        return response()->json([
            'message' => 'Updated successfully',
            'data' => new SourceResource($source)
        ]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        // Find the source or return a 404 response if not found
        $source = Source::find($id);

        Gate::authorize('delete', $source);
        
        if (!$source) {
            return response()->json(['message' => 'Source not found'], 404);
        }

        // Authorization check
        Gate::authorize('delete', $source);

        // Delete the source
        $source->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }

}
