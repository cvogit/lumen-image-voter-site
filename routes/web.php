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

$router->get('test', 		'RegisterController@test');
$router->get('/', function () {
    return "Lumen image voting server.";
});

$router->post('register', 'RegisterController@register');

$router->get('login', 		'AuthController@login');

$router->get('posts', 							'PostController@get');
$router->get('images/{imageId}', 		'ImageController@get');
$router->put('verification/{verificationCode}', 		'AuthController@verify');


// JWT protected routes
$router->group(['middleware' => ['jwt', 'user']], function () use ($router) {

	$router->get('refresh', 		'AuthController@refresh');

	$router->post('posts',			'PostController@create');

	$router->put('posts/{postId}/vote/{newVote}',			'PostController@vote');

});
