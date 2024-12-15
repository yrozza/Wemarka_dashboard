<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Controller;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::apiResource('clients',ClientController::class);
Route::apiResource('client.sources',SourceController::class);
Route::apiResource('sources',SourceController::class);




Route::get('/clients/name/{client_name}', [ClientController::class, 'showByName']);


Route::patch('/clients/{id}/update-source', [ClientController::class, 'updateSource']);





Route::patch('clients/{client}', [ClientController::class, 'update']);
Route::put('/clients/{client}', [ClientController::class, 'update']);
