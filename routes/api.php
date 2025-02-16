<?php
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VarientController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;



////////////// Routes for clients

Route::middleware('auth:sanctum') -> group(function(){
    Route::get('/clients/name/{client_name}', [ClientController::class, 'showByName']);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('client.sources', SourceController::class);
    Route::put('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('clients', [ClientController::class, 'destroy']);
});




////////////// Routes for sources
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('sources', SourceController::class);
    Route::get('sources/id/{id}', [SourceController::class, 'showId']);
});


//////////////Routes for shipping companies
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('shippings', ShippingController::class);
    Route::get('shippings/name/{Shipping_name}', [ShippingController::class, 'showByName']);
});



//////////////Routes for Employees
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('employee', EmployeeController::class);
    Route::get('employee/name/{Employee_name}', [EmployeeController::class, 'showByName']);
});



//////////////Route for Cites
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cities', CityController::class);
    Route::get('cities/name/{City_name}', [CityController::class, 'showByName']);
    });



//////////////Routes for Area 
Route::middleware('auth:sanctum')->group(function(){
Route::apiResource('area', AreaController::class);
Route::get('area/name/{Area_name}', [AreaController::class, 'showByName']);
});

/////////////Routes for brands
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('brand', BrandController::class);
    Route::get('brand/name/{Brand_name}', [BrandController::class, 'showByname']);
});






/////////////////////Routes for Category
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('category', CategoryController::class);
    Route::get('category/name/{Category}', [CategoryController::class, 'showByName']);
});




////////////////////Routes for Products
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('product', ProductController::class);
    Route::get('/products-with-variants', [ProductController::class, 'getAllProductsWithVariants']);
    Route::get('product/name/{Product_name}', [ProductController::class, 'showByName']);
    Route::delete('/products', [ProductController::class, 'destroy']);
});


/////////////////////Routes for Supplier
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('supplier', SupplierController::class);
    Route::get('supplier/name/{Supplier_name}', [SupplierController::class, 'showByName']);
});



Route::post('variant/{product}/{variant}/image', [VarientController::class, 'updateVariantImage'])
    ->middleware('auth:sanctum');


////////////////////Routes for Varients
    Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('product.varient', VarientController::class)->scoped(['varient' => 'id']);
    Route::get('/product/{product}/varient/{varient}', [VarientController::class, 'showProductwithVariant']);
    Route::post('/product/{product}/varient/{varient}/Add-Image', [VarientController::class, 'addImage']);
    // Route::patch('/variant/{variantId}/edit-image/{imageId}', [VarientController::class, 'updateVariantImage']);
    // Route::middleware('auth:sanctum')->put('/products/{product}/variants/{id}/image', [VarientController::class, 'updateVariantImage']);
    Route::apiResource('products.variants.images', ImageController::class)
    ->scoped([
        'variant' => 'id',
        'image' => 'id', // Optional, ensures the `image` route uses `id` for lookup
    ]);
    Route::delete('products/{product}/variants/destroy-selected', [VarientController::class, 'destroySelectedVarients']);


});





Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('packages', PackageController::class);
    Route::get('/packages/search/{name}', [PackageController::class, 'searchByName']);
    Route::patch('packages/{id}/basic-info', [PackageController::class, 'updateBasicInfo']); // Update Name, Description, and Price
    Route::patch('packages/{packageId}/variants/{variantId}', [PackageController::class, 'replaceVariant']);
    Route::delete('packages/{packageId}/variants', [PackageController::class, 'deleteVariants']);

});










////////////////////////////Routes For Cart
Route::middleware('auth:sanctum')->group(function(){
Route::apiResource('carts', CartController::class);
Route::get('carts/ClientName/{client_name}',[CartController::class, 'showByName']);
    Route::patch('carts/{cart}/checkout', [CartController::class, 'checkout']);
Route::scopeBindings()->group(function () {
        Route::apiResource('carts.items', ItemController::class)->scoped([
            'cart' => 'id',  // Ensure the `cart` is bound by `id`
            'item' => 'id',  // Ensure the `cartItem` is bound by `id`
        ]);
        Route::patch('cart-items/{cartItem}', [ItemController::class, 'update']);
});
});







/////////////////////////////Routes for Order

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/orders', [OrderController::class, 'getAllOrders']);
    Route::get('/orders-report', [OrderController::class, 'getOrderReport']);
    Route::get('order-selected/generate-report', [OrderController::class, 'generateOrderReport']);
    Route::get('/order/{orderId}/ticket', [OrderController::class, 'getOrderInfo']);
    Route::get('orders/{id}', [OrderController::class, 'getOrderById']); 
    Route::get('/order/{orderId}/customer-info', [OrderController::class, 'getCustomOrderInfo']);
    Route::get('/orders-report', [OrderController::class, 'getOrderReport']);
    Route::patch('orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
    Route::patch('orders/{id}/shipping-status', [OrderController::class, 'updateShippingStatus']);

    Route::delete('orders/{id}', [OrderController::class, 'deleteOrder']);
});


//////////////////////QR CODE
Route::get('generate-qr/{order_id}', [QRCodeController::class, 'generateQRCode']);
Route::get('/order/{orderId}/pdf', [PDFController::class, 'generatePdf']);




////////////////////////////////////////Routes for authentication


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/registeeeer', [AuthController::class, 'register']);
});

Route::post('/register', [UserController::class, 'register'])->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->patch('/user/update', [AuthController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);
Route::middleware('auth:sanctum')->post('/update-profile-pic', [AuthController::class, 'updateProfilePic']);




Route::middleware(['auth:sanctum', 'role:super-admin|admin'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});













