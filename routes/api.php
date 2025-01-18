<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VarientController;
use App\Http\Controllers\Api\ImageController;
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

////////////// Routes for sources
Route::apiResource('sources', SourceController::class);
Route::get('sources/id/{id}', [SourceController::class, 'showId']);

//////////////Routes for shipping companies
Route::apiResource('shippings', ShippingController::class);
Route::get('shippings/name/{Shipping_name}', [ShippingController::class, 'showByName']);


//////////////Routes for Employees

Route::apiResource('employee', EmployeeController::class);
Route::get('employee/name/{Employee_name}', [EmployeeController::class, 'showByName']);


//////////////Route for Cites
Route::apiResource('cities', CityController::class);
Route::get('cities/name/{City_name}', [CityController::class, 'showByName']);


//////////////Routes for Area 
Route::apiResource('area', AreaController::class);
Route::get('area/name/{Area_name}', [AreaController::class, 'showByName']);
// Route::patch('area/{area}', [AreaController::class, 'updateOnlyone']);


/////////////Routes for brands
Route::apiResource('brand', BrandController::class);
Route::get('brand/name/{Brand_name}', [BrandController::class, 'showByname']);
Route::patch('brand/{brand}', [BrandController::class, 'updateOnlyone']);




/////////////////////Routes for Category
Route::apiResource('category', CategoryController::class);
Route::get('category/name/{Category}', [CategoryController::class, 'showByName']);
Route::patch('category/{category}', [CategoryController::class, 'updateOnlyone']);




////////////////////Routes for Products
Route::apiResource('product', ProductController::class);
Route::get('product/name/{Product_name}', [ProductController::class, 'showByName']);
Route::patch('product/{product}', [ProductController::class, 'updateOnlyone']);
Route::get('/products-with-variants', [ProductController::class, 'getAllProductsWithVariants']);




////////////////////Routes for Varients
Route::apiResource('product.varient', VarientController::class)
    ->scoped(['varient' => 'id']);
Route::post('/product/{product}/varient/{varient}/Add-Image', [VarientController::class, 'addImage']);
Route::patch('/variant/{variantId}/edit-image/{imageId}', [VarientController::class, 'updateImage']);



////////////////////Routes for images
Route::apiResource('products.variants.images', ImageController::class)
    ->scoped([
        'variant' => 'id',
        'image' => 'id', // Optional, ensures the `image` route uses `id` for lookup
    ]);



// Route::middleware(['auth', 'admin'])->group(function () {
//     Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
//     Route::post('/admin/create-user', [AdminController::class, 'createUser']);
//     Route::delete('/admin/delete-user/{id}', [AdminController::class, 'deleteUser']);
// });




Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);

