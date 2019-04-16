<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('orders', 'OrderController@create');
Route::get('orders', 'OrderController@list');
Route::get('orders/{order}', 'OrderController@get');
Route::delete('orders/{order}', 'OrderController@delete');
Route::put('orders/{order}', 'OrderController@update');
Route::post('orders/{order}/add', 'OrderController@attach');
Route::post('orders/{order}/pay', 'OrderController@pay');
