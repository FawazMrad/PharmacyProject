<?php
use App\Http\Controllers\AdminAuth;
use App\Http\Controllers\Med;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PharAuth;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/pharmacist/signup', [PharAuth::class, 'signUp']); //pharmacist signup
Route::post('/pharmacist/login', [PharAuth::class, 'login']); //pharmacist login
Route::post('/admin/login', [AdminAuth::class, 'login']); //admin login
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function () {
        return response()->json(['message' => Auth::user()]);
    });
    Route::post('/pharmacist/logout', [PharAuth::class, 'logout']);  // pharmacist logout
    Route::post('/admin/logout', [AdminAuth::class, 'logout']);  // admin logout
    Route::post('/medicine/add', [Med::class, 'add']); //  add medicine for the warehouse owner
    Route::get('/pharmacist/browse/categories',[Med::class,'browseCategories']); // browsing categories
    Route::get('/pharmacist/browse/meds',[Med::class,'browseMedsByCat']); // browsing meds
    Route::get('/search',[Med::class,'searchList']);  //pharmacist or warehouse owner search        method name:search/searchList
    Route::get('/medicine/show',[Med::class,'showMedSpec']); //pharmacist or warehouse owner search
    Route::post('/pharmacist/create/order',[OrderController::class,'createOrder']); // pharmacist creating order
    Route::get('/pharmacist/browse/orders',[OrderController::class,'browsePharOrders']); // pharmacist browsing orders
    Route::get('/admin/browse/orders',[OrderController::class,'browseAdminOrders']);  // admin browsing orders
    Route::patch('/admin/change/order/status',[OrderController::class,'changeOrderStatus']); // admin changing order status
    Route::patch('/admin/change/order/payment/status',[OrderController::class,'changeOrderPaymentStatus']); // admin changing order payment status
    Route::post('/medicine/change/favourite/status',[Med::class,'changeFavStatus']); //pharmacist change favourite status
    Route::get('/pharmacist/browse/favourites',[Med::class,'browseFavourites']); //  pharmacist browse favourites
    Route::get('/admin/history',[OrderController::class,'history']);// admin history
});

