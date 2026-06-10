<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\accountsController;
use App\Http\Controllers\adsController;
use App\Http\Controllers\followersController;
use App\Http\Controllers\groupsController;
use App\Http\Controllers\likesController;
use App\Http\Controllers\notificationsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ourProductsController;
use App\Http\Controllers\requestsController;
use App\Http\Controllers\usersController;
use App\Models\Products;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/check-token', function (Request $request) {
    return response()->json(['user' => $request->user()]);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
    Route::get('user',[App\Http\Controllers\usersController::class,'user']);

    Route::apiResource('notification',notificationsController::class);

  Route::resource('ads', App\Http\Controllers\adsController::class); 
//Route::post('im',[adsController::class,'store']);
Route::post('register',[App\Http\Controllers\usersController::class,'register']);
Route::post('login',[App\Http\Controllers\usersController::class,'login']);
Route::middleware(['auth:api'])->group(function (){
    Route::get('userinfo',[App\Http\Controllers\usersController::class,'userInfo']);
    Route::post('userupdate',[App\Http\Controllers\usersController::class,'update']);

    Route::get('product/search', [ProductsController::class,'search']);
    Route::get('ourproduct/search', [ourProductsController::class,'search']);
    Route::get('product/getUserProduct', [ProductsController::class,'getUserProduct']);
    Route::apiresource('product', ProductsController::class);
    Route::get('indexOrder', [App\Http\Controllers\ProductsController::class,'indexOrder']);

    Route::apiresource('order', App\Http\Controllers\OrderController::class);

    Route::resource('group', App\Http\Controllers\groupsController::class);
    Route::apiresource('follower', App\Http\Controllers\followersController::class);
    Route::resource('comment', App\Http\Controllers\commentController::class);

    Route::resource('likes', App\Http\Controllers\likesController::class);
    Route::resource('account', App\Http\Controllers\accountsController::class);
    Route::resource('ourproduct', App\Http\Controllers\ourProductsController::class);
    Route::resource('request', App\Http\Controllers\requestsController::class);
    Route::apiresource('image', App\Http\Controllers\imageController::class);

    
});