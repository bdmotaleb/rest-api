<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group([
    'prefix' => 'api/v1'
], function () use ($router) {
    # User Authentication
    $router->post('/login', 'UsersController@authenticate');

    # Users
    $router->post('/users', 'UsersController@create');

    # Restricted Routes
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        # Users
        $router->get('/users', 'UsersController@index');
        $router->get('/users/{id}', 'UsersController@show');
        $router->get('/users/profile', 'UsersController@profile');
        $router->post('/users/update', 'UsersController@update');
        $router->delete('/users/{id}', 'UsersController@destroy');
    });

});
