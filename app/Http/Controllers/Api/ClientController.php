<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\client;
use App\Models\Source;
use App\Models\City;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Set the default number of clients per page
        $perPage = $request->query('per_page', 10); // Default to 10 per page

        // Use Query Builder to fetch clients with pagination
        $clients = DB::table('clients')
        ->leftJoin('sources', 'clients.source_id', '=', 'sources.id') // Join the 'sources' table
        ->select(
            'clients.id',
            // Use conditional statements to check if area_id or city_id is null
            DB::raw("IFNULL(clients.area_id, 'Area not provided') as client_area"),
            DB::raw("IFNULL(clients.city_id, 'City not provided') as client_city"),
            'clients.client_name',
            'clients.client_age',
            'clients.client_email',
            'clients.client_phonenumber',
            'sources.Source_name', // Select the 'Source_name' from the sources table
            DB::raw("IFNULL(clients.source_link, 'Source link not provided') as source_link"), // Handle null for source_link
            'clients.created_at',
            'clients.updated_at'
        )
            ->paginate($perPage); // Apply pagination

        // Return the response with the paginated data
        return response()->json($clients);
    }





    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        // Use Query Builder to fetch the data with a JOIN on the 'sources' table
        $client = DB::table('clients')
        ->leftJoin('sources', 'clients.source_id', '=', 'sources.id') // Join the 'sources' table
        ->select(
            'clients.id',
            // Use conditional statements to check if area_id or city_id is null
            DB::raw("IFNULL(clients.area_id, 'Area not provided') as client_area"),
            DB::raw("IFNULL(clients.city_id, 'City not provided') as client_city"),
            'clients.client_name',
            'clients.client_age',
            'clients.client_email',
            'clients.client_phonenumber',
            'sources.Source_name', // Select the 'Source_name' from the sources table
            DB::raw("IFNULL(clients.source_link, 'Source link not provided') as source_link"), // Handle null for source_link
            'clients.created_at',
            'clients.updated_at'
        )
            ->where('clients.client_name', 'LIKE', "%$client_name%") // Search for client name using LIKE
            ->get(); // Get all matching records

        // Check if clients exist
        if ($client->isEmpty()) {
            return response()->json(['message' => 'No clients found'], 404);
        }

        // Return the response with the data
        return response()->json($client);
    }





    public function show($id)
    {
        // Use Query Builder to fetch the data with a JOIN on the 'sources' table
        $clients = DB::table('clients')
        ->leftJoin('sources', 'clients.source_id', '=', 'sources.id') // Join the 'sources' table
        ->select(
            'clients.id',
            // Use conditional statements to check if area_id or city_id is null
            DB::raw("IFNULL(clients.area_id, 'Area not provided') as client_area"),
            DB::raw("IFNULL(clients.city_id, 'City not provided') as client_city"),
            'clients.client_name',
            'clients.client_age',
            'clients.client_email',
            'clients.client_phonenumber',
            'sources.Source_name', // Select the 'Source_name' from the sources table
            DB::raw("IFNULL(clients.source_link, 'Source link not provided') as source_link"), // Handle null for source_link
            'clients.created_at',
            'clients.updated_at'
        )
            ->where('clients.id', $id) // Correctly use the variable
            ->get(); // Get all matching records

        // Check if clients exist
        if ($clients->isEmpty()) {
            return response()->json(['message' => 'No clients found'], 404);
        }

        // Return the response with the data
        return response()->json($clients);
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate incoming request
        $validated = $request->validate([
            'client_name' => 'nullable|string|max:255|unique:clients,client_name,' . $id,  // Allow null but ensure uniqueness except for current client
            'client_age' => 'nullable|integer',
            'client_email' => 'nullable|email|unique:clients,client_email,' . $id,
            'client_phonenumber' => 'nullable|string|unique:clients,client_phonenumber,' . $id,
            'area_id' => 'nullable|exists:areas,id',  // Ensure the area_id is valid
            'city_id' => 'nullable|exists:cities,id',  // Ensure the city_id is valid
            'source_id' => 'nullable|exists:sources,id',  // Ensure source_id is valid
            'source_link' => 'nullable|url',
        ]);

        // Find the client by ID
        $client = Client::find($id);

        // Check if the client exists
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        // Handle area and city changes
        if (isset($validated['area_id'])) {
            // Get the area based on the area_id
            $area = Area::find($validated['area_id']);

            // Check if area exists, if not, return an error
            if (!$area) {
                return response()->json(['message' => 'Area not found'], 404);
            }

            // Set the area_name from the area table
            $validated['area_name'] = $area->Area_name;

            // Automatically set the city_id to the first city in that area (or adjust based on your logic)
            $city = $area->city()->first();  // Assuming `Area` model has a relationship to `City`
            if ($city) {
                $validated['city_id'] = $city->id;
                $validated['city_name'] = $city->City_name;
            } else {
                $validated['city_id'] = null;  // No cities found in the area
                $validated['city_name'] = 'No cities available';  // Adjust accordingly
            }
        }

        // If city_id is provided, check if it matches the area_id
        if (isset($validated['city_id'])) {
            $city = City::find($validated['city_id']);

            if (!$city) {
                return response()->json(['message' => 'City not found'], 404);
            }

            // Check if the city belongs to the selected area
            if ($city->area_id != $validated['area_id']) {
                return response()->json(['message' => 'The selected city does not belong to the specified area'], 400);
            }

            $validated['city_name'] = $city->City_name;
        }

        // If source_link is provided as null, set it to 'Source link is not provided'
        if (empty($validated['source_link'])) {
            $validated['source_link'] = 'Source link is not provided';
        }

        // Update the client record
        $client->update($validated);

        // Return the updated client with the associated source_name
        return response()->json([
            'message' => 'Client updated successfully!',
            'client' => $client,
        ], 200);
    }


    
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'message'=>'Client deleted successfully'
        ]);

    }
}
