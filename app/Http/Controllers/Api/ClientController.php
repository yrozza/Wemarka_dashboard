<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\client;
use App\Models\Source;
use App\Models\City;
use App\Models\Area;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        // Authorization check
        if (!Gate::allows('viewAny', Client::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get the number of clients per page (default to 10)
        $perPage = $request->query('per_page', 10);

        // Retrieve clients with related data
        $clients = Client::with(['source', 'area', 'city'])->paginate($perPage);

        // Return clients using the resource
        return ClientResource::collection($clients);
    }





    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if the user is authorized to create a client
        Gate::authorize('create', Client::class);
        // Validate incoming request
        $validated = $request->validate([
            'client_name' => 'required|string|max:255|unique:clients,client_name',
            'client_age' => 'required|integer',
            'client_email' => 'required|email|unique:clients,client_email',
            'client_phonenumber' => 'required|string|unique:clients,client_phonenumber',
            'area_id' => 'required|exists:areas,id',  // Ensure the area_id is valid
            'city_id' => 'required|exists:cities,id',  // Ensure the city_id is valid
            'source_id' => 'required|exists:sources,id',  // Ensure source_id is valid
            'source_link' => 'required|url',
        ]);

        // Find the area and city by their IDs
        $area = Area::find($validated['area_id']);
        $city = City::find($validated['city_id']);

        // Assign the area_name and city_name from the area and city tables
        $validated['area_name'] = $area->Area_name;  // Assuming Area has a 'name' column
        $validated['city_name'] = $city->City_name;  // Assuming City has a 'name' column

        // If source_link is null, set it to 'Source link is not provided'
        if (!$validated['source_link']) {
            $validated['source_link'] = 'Source link is not provided';
        }


        // Create the client record
        $client = Client::create($validated);

        // Return the created client with the associated source_name
        return response()->json([
            'message' => 'Client created successfully!',
            'client' => $client,
        ], 201);
    }




    /**
     * Display the specified resource.
     */




    public function showByName($client_name)
    {
        // Check authorization using policy
        Gate::authorize('viewAny', Client::class);

        // Fetch clients by name with relationships
        $clients = Client::with(['source'])
        ->where('client_name', 'LIKE', "%$client_name%")
        ->get();

        // Check if clients exist
        if ($clients->isEmpty()) {
            return response()->json(['message' => 'No clients found'], 404);
        }

        // Return results using a Resource
        return ClientResource::collection($clients);
    }







    public function show($id)
    {

        $client = Client::with('source')->find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        if (!Gate::allows('view', $client)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new ClientResource($client);
    }









    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        
        // Validate incoming request
        $validated = $request->validate([
            'client_name' => 'nullable|string|max:255|unique:clients,client_name,' . $id,
            'client_age' => 'nullable|integer',
            'client_email' => 'nullable|email|unique:clients,client_email,' . $id,
            'client_phonenumber' => 'nullable|string|unique:clients,client_phonenumber,' . $id,
            'area_id' => 'nullable|exists:areas,id',
            'city_id' => 'nullable|exists:cities,id',
            'source_id' => 'nullable|exists:sources,id',
            'source_link' => 'nullable|url',
        ]);

        // Find the client by ID
        $client = Client::find($id);
        Gate::authorize('update', $client);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        // Check if area_id and city_id are both provided
        if (isset($validated['area_id']) && isset($validated['city_id'])) {
            $area = Area::find($validated['area_id']);

            // Check if the area belongs to the provided city_id
            if ($area->city_id != $validated['city_id']) {
                return response()->json(['message' => 'The selected area does not belong to the specified city'], 400);
            }

            // Assign area_name and city_name
            $validated['area_name'] = $area->Area_name;
            $city = City::find($validated['city_id']);
            $validated['city_name'] = $city->City_name;
        }

        // Handle cases where only one of area_id or city_id is provided
        if (isset($validated['area_id']) && !isset($validated['city_id'])) {
            $area = Area::find($validated['area_id']);
            $validated['area_name'] = $area->Area_name;

            $city = $area->city;
            $validated['city_id'] = $city->id;
            $validated['city_name'] = $city->City_name;
        }

        if (isset($validated['city_id']) && !isset($validated['area_id'])) {
            $city = City::find($validated['city_id']);
            $validated['city_name'] = $city->City_name;

            return response()->json(['message' => 'Area is required when providing a city'], 400);
        }

        // Handle source_link
        if (empty($validated['source_link'])) {
            $validated['source_link'] = 'Source link is not provided';
        }

        // Update the client record
        $client->update($validated);

        return response()->json([
            'message' => 'Client updated successfully!',
            'client' => $client,
        ], 200);
    }






    public function destroy(Request $request, $client = null)
    {
        try {
            // If a single client ID is provided
            if ($client) {
                $client = Client::findOrFail($client); // Find client or throw 404

                // Authorization check for deleting a specific client
                Gate::authorize('delete', $client);

                $client->delete();

                return response()->json([
                    'message' => 'Client deleted successfully'
                ], 200);
            }

            // Validate request for bulk deletion
            $validated = $request->validate([
                'client_ids' => 'required|array',
                'client_ids.*' => 'exists:clients,id',
            ]);

            // Fetch clients to be deleted
            $clients = Client::whereIn('id', $validated['client_ids'])->get();

            // Authorization check for each client
            foreach ($clients as $client) {
                Gate::authorize('delete', $client);
            }

            // Proceed with bulk deletion
            Client::whereIn('id', $validated['client_ids'])->delete();

            return response()->json([
                'message' => 'Clients deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting clients',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




}
