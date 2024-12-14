<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\client;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    return Client::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $client = Client::create([
        ...$request->validate([
            'client_name'=>'required|string|max:255',
            'client_age'=>'required|integer|max:100',
            'client_area'=>'required|string|max:255',
            'client_city' => 'required|string|max:255',
            'client_email' =>'required|email|unique:clients,client_email',
            'client_phonenumber' =>'required|regex:/^\+?[0-9]{1,4}?[0-9]{7,15}$/|unique:clients,client_phonenumber'
        ]),'source_id'=>1
]);
    return new ClientResource($client);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return $client;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $client->update(  
            $request->validate([
            'client_name'=>'required|string|max:255',
            'client_age'=>'required|integer|max:100',
            'client_area'=>'required|string|max:255',
            'client_city' => 'required|string|max:255',
            'client_email' =>'required|email|unique:clients,client_email',
            'client_phonenumber' =>'required|regex:/^\+?[0-9]{1,4}?[0-9]{7,15}$/|unique:clients,client_phonenumber'
            ])
            );
        return new ClientResource($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'message'=>'Client deleted successfully'
        ]);

    }
}
