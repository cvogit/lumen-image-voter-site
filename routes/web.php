<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    return "Hello";
});

$router->post('register', 'RegisterController@register');

$router->get('login', 		'AuthController@login');


// JWT protecteed routes
$router->group(['middleware' => 'jwt'], function () use ($router) {

	
});