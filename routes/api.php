<?php

Route::get('categories', 'CategoriesController@index');
Route::get('manufacturers', 'ManufacturersController@index');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
