<?php

Route::prefix('v2')->namespace('V2')->group(function () {
    Route::get('region-products', 'RegionProductsController@index');
    Route::get('products/list-with-prices', 'ProductsController@getListWithPrices');
});

Route::get('categories', 'CategoriesController@index');
Route::get('manufacturers', 'ManufacturersController@index');
Route::get('colors', 'ColorsController@index');
Route::get('categories/{category}/attributes', 'CategoryAttributesController@index');
Route::get('combos', 'CombosController@index');
Route::get('combos/{combo}/detail', 'CombosController@detail');
Route::get('productConfigurables', 'ProductConfigurablesController@index');
Route::get('products/search', 'ProductsController@search');
Route::get('suppliers/search', 'SuppliersController@search');
Route::get('products', 'ProductsController@index');
Route::get('products/getConfigurableList', 'ProductsController@getConfigurableList');
Route::get('products/min-import-price-list', 'ProductsController@getMinImportPriceList');
Route::get('products/get-quotation','ProductsController@getProductQuotation');
Route::get('products/get-product-with-cate-manu','ProductsController@getProductWithCategoryManufacturer');
Route::get('products/{product}', 'ProductsController@show');
Route::get('products/{id}/detail', 'ProductsController@detail');
Route::get('listProductSku', 'ProductsController@getListProductSku');
Route::get('listSupplierByProductId', 'SuppliersController@getListSupplierByProductId');
Route::get('listBundleByProvinceCode/{codeProvince}', 'BundlesController@listBundleByProvinceCode');
Route::get('listBundleProduct/{bundleId}', 'BundlesController@getBundleProduct');
Route::get('version', 'VersionController@index');
Route::get('marginOrders', 'MarginsController@index');

// Transport Fees
Route::get('transport-fees', 'TransportFeesController@index');
Route::put('provinces/{province}/transport-fee', 'ProvinceTransportFeeController@update');

Route::post('products/create-from-google-sheet', 'ProductsController@createFromGoogleSheet');
Route::get('suppliers', 'SuppliersController@index');
Route::get('suppliers/regions/', 'SuppliersController@getSuppliersByRegionId');
Route::post('products/imports/import-from-google-sheet', 'ProductImportsController@importFromGoogleSheet');
Route::post('products/update-name-by-sku','ProductsController@updateNameBySku');
Route::post('suppliers/update-price-valid-time-google-sheet','SuppliersController@updatePriceValidTimeFromGoolgeSheet');



Route::group(['middleware' => 'auth:api'], function () {
    Route::get('user', 'UserController@show');

    Route::post('products/suppliers/update-price-from-google-sheet', 'ProductSuppliersController@updatePriceFromGoolgeSheet');

    Route::post('products/deactivate-errors-from-google-sheet', 'ProductsController@deactivateErrorsFromGoogleSheet');

    Route::get('product-suppliers', 'ProductSuppliersController@index');
    Route::get('product-suppliers/get-ids', 'ProductSuppliersController@getIds');
    Route::post('product-suppliers/exportExcel', 'ProductSuppliersController@exportExcel');
});
