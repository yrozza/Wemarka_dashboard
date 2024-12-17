<?php
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Middleware-protected user route
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

////////////// Routes for clients
Route::get('/clients/name/{client_name}', [ClientController::class, 'showByName']);
Route::apiResource('clients', ClientController::class);
Route::apiResource('client.sources', SourceController::class); // Relation between clients and sources
Route::put('/clients/{client}', [ClientController::class, 'update']); // Use PUT for updates

///////////////// Routes for sources
Route::apiResource('sources', SourceController::class);
Route::get('sources/id/{id}',[SourceController::class, 'showId']);

//// Routes for shipping companies
Route::apiResource('shippings',ShippingController::class);
Route::get('shippings/name/{Shipping_name}', [ShippingController::class, 'showByName']);


// Routes for Employees

Route::apiResource('employee',EmployeeController::class);
Route::get('employee/name/{Employee_name}',[EmployeeController::class, 'showByName']);


//Routes for Area 
Route::apiResource('area',AreaController::class);
Route::get('area/name/{Area_name}',[AreaController::class,'showByName']);