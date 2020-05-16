<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
// $router->post('/login', 'UsersController@login');
Route::post('/login','UsersController@login');

$router->group(['prefix'=>'apps','middleware' => 'auth:sanctum'],function() use ($router){
    $router->get('/user','UsersController@profile');
});
