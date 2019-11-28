<?php
Route::any('/','client\meau\IndexController@index')->name('UserController.List');//列表


Route::group(['middleware'=>['check','auth.timeout'],'namespace'=>'client'],function(){ //客户端
    Route::group(['prefix'=>'meau','namespace'=>'meau','middleware'=>['auth.timeout']],function(){ //菜单1
        Route::any('List','UserController@showProfile')->name('UserController.List');//列表
    });

});



