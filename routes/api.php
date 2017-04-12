<?php

Route::get('categories', 'CategoriesController@index');
Route::get('manufacturers', 'ManufacturersController@index');
Route::get('products', 'ProductsController@index');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
