<?php

Route::get('categories', 'CategoriesController@index');
Route::get('manufacturers', 'ManufacturersController@index');
Route::get('categories/{category}/attributes', 'CategoryAttributesController@index');
Route::get('products', 'ProductsController@index');
Route::get('products/{product}', 'ProductsController@show');
Route::get('products/{id}/detail', 'ProductsController@detail');
Route::get('listProductSku', 'ProductsController@getListProductSku');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
