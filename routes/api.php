<?php

Route::get('categories', 'CategoriesController@index');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
