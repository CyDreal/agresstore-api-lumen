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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Add this before the api group
$router->get('/storage/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return response($file, 200)->header('Content-Type', $type);
});

$router->group(['prefix' => 'api'], function () use ($router) {
    // Auth
    $router->post('register', 'UserController@register');
    $router->post('login', 'UserController@login');

    // User Management
    $router->get('user', 'UserController@index');
    $router->get('user/{id}', 'UserController@show');
    $router->put('user/{id}', 'UserController@update');
    // $router->put('users/{id}/avatar', 'UserController@updateAvatar');
    $router->delete('user/{id}', 'UserController@delete');

    // Product Management
    $router->get('/products', 'ProductController@index');
    $router->get('/products/{id}', 'ProductController@show');
    // $router->post('/products', 'ProductController@store');
    // $router->put('/products/{id}', 'ProductController@update');
    // $router->delete('/products/{id}', 'ProductController@destroy');
    $router->post('/products/view-count', 'ProductController@updateViewCount');
    // $router->get('/products/{productId}/variants', 'ProductController@getVariants');
});
