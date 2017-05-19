<?php

Route::get('categories', 'CategoriesController@index');
Route::get('manufacturers', 'ManufacturersController@index');
Route::get('colors', 'ColorsController@index');
Route::get('categories/{category}/attributes', 'CategoryAttributesController@index');
Route::get('products', 'ProductsController@index');
Route::get('products/{product}', 'ProductsController@show');
Route::get('products/{id}/detail', 'ProductsController@detail');
Route::get('listProductSku', 'ProductsController@getListProductSku');
Route::get('listSupplierByProductId', 'SuppliersController@getListSupplierByProductId');
Route::get('listBundleByCodeProvince/{codeProvince}', 'BundlesController@listBundleByCodeProvince');
Route::get('listBundleProduct/{bundleId}', 'BundlesController@getBundleProduct');
Route::get('version', 'VersionController@index');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
