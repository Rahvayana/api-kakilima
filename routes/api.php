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

    $router->post('/addfoto','UsersController@profile');
    $router->get('/user','UsersController@profile');

    $router->group(['prefix'=>'profil'],function() use ($router){
        $router->get('/status','UsersController@statusUser');
    });

    $router->group(['prefix'=>'seller'],function() use ($router){
        $router->get('/index','SellerController@index');
        $router->post('/addSeller','SellerController@addSeller');
    });

});

$router->group(['prefix'=>'register'],function() use ($router){
    $router->post('/sendotp','UsersController@sendOTP');
    $router->post('/resendotp','UsersController@resendOTP');
    $router->post('/cekotp','UsersController@cekOTP');
    $router->post('/addprofil','UsersController@addProfil');
});
