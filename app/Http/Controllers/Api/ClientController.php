<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\client;
use App\Models\Source;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Use Query Builder to fetch all clients with a JOIN on the 'sources' table
        $clients = DB::table('clients')
        ->leftJoin('sources', 'clients.source_id', '=', 'sources.id') // Join the 'sources' table
        ->select(
            'clients.id',
            'clients.client_name',
            'clients.client_age',
            'clients.client_email',
            'clients.client_phonenumber',
            'clients.client_area',
            'clients.client_city',
            'sources.Source_name', // Select the 'Source_name' from the sources table
            'clients.source_link', // Select the 'source_link' from the clients table
            'clients.created_at',
            'clients.updated_at'
        )
            ->get(); // Get all records

        // Return the response with the data
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
            'client_area' => 'required|string',
            'client_city' => 'required|string',
            'source_name' => 'required|string',
            'source_link' => 'nullable|url', // Validate source_link as a URL
        ]);

        // Find the source by name (or create if not exists)
        $source = Source::where('Source_name', $validated['source_name'])->first();

        if (!$source) {
            return response()->json(['message' => 'Source not found'], 404);
        }

        // Create the client record with the source_id and source_link
        $client = new Client();
        $client->client_name = $validated['client_name'];
        $client->client_age = $validated['client_age'];
        $client->client_email = $validated['client_email'];
        $client->client_phonenumber = $validated['client_phonenumber'];
        $client->client_area = $validated['client_area'];
        $client->client_city = $validated['client_city'];
        $client->source_id = $source->id; // Assign the source_id
        $client->source_link = $validated['source_link']; // Assign the source_link
        $client->save();

        // Return the created client with the associated source_name
        return response()->json([
            'message' => 'Client created successfully!',
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
            'clients.client_name',
            'clients.client_age',
            'clients.client_email',
            'clients.client_phonenumber',
            'clients.client_area',
            'clients.client_city',
            'sources.Source_name', // Select the 'Source_name' from the sources table
            'clients.source_link', // Select the 'source_link' from the clients table
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
            'clients.client_name',
            'clients.client_age',
            'clients.client_email',
            'clients.client_phonenumber',
            'clients.client_area',
            'clients.client_city',
            'sources.Source_name', // Select the 'Source_name' from the sources table
            'clients.created_at',
            'clients.updated_at'
        )
            ->where('clients.id', $id)  // Correctly use the variable
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
    public function update(Request $request, Client $client)
    {
        try {
            $client->update(
                $request->validate([
                    'client_name' => 'required|string|max:255|unique:clients,client_name',
                    'client_age' => 'required|integer',
                    'client_email' => 'required|email|unique:clients,client_email',
                    'client_phonenumber' => 'required|string|unique:clients,client_phonenumber',
                    'client_area' => 'required|string',
                    'client_city' => 'required|string',
                    'source_name' => 'required|string',
                    'source_link' => 'nullable|url', // Validate source_link as a URL
                ])
            );

            return response()->json([
                'message' => 'Updated successfully',
                'data' => $client
            ]);
        } catch (\Exception $e) {
            // Catch any unexpected errors and return a 500 response with the error message
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'message'=>'Client deleted successfully'
        ]);

    }
}
