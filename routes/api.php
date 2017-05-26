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
Route::get('listBundleByProvinceCode/{codeProvince}', 'BundlesController@listBundleByProvinceCode');
Route::get('listBundleProduct/{bundleId}', 'BundlesController@getBundleProduct');
Route::get('version', 'VersionController@index');
Route::get('marginOrders', 'MarginsController@index');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
