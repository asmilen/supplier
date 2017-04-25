<?php

Route::get('categories', 'CategoriesController@index');
Route::get('manufacturers', 'ManufacturersController@index');
Route::get('products', 'ProductsController@index');
Route::get('products/{id}/detail', 'ProductsController@detail');
Route::get('listProductSku', 'ProductsController@getListProductSku');
Route::get('listSupplierByProductId', 'SuppliersController@getListSupplierByProductId');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
