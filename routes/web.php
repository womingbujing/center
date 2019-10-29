<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::any('fang', function () {
    return 'www.cainiaofcf.cn';
});


/*Route::get('user/{id}', function ($id) {
    return 'User '.$id;
});*/

/*Route::get('user/{name?}', function ($name = 'John') {
    return $name;
});*/

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        // 使用 Auth 中间件
    });

    Route::get('user/profile', function () {
        // 使用 Auth 中间件
    });
});

Route::get('user/profile', function () {
    return 'profile';
})->name('profile');

Route::get('user/{id}', 'UserController@showProfile');

