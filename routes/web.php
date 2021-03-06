<?php

Route::get('/', function () {
    return redirect('/dashboard');
});

Auth::routes();

Route::get('auth/google', 'Auth\AuthController@redirectToProvider');
Route::get('auth/google/callback', 'Auth\AuthController@handleProviderCallback');
Route::get('auth/teko/callback', 'Auth\AuthController@handleTekoCallback');

Route::get('provinces', 'ProvincesController@index');
Route::get('provinces/{province}/address-code', 'ProvincesController@getAddressCode');
Route::get('provinces/{province}/districts', 'ProvinceDistrictsController@index');
Route::get('region/{bundle}/products', 'BundlesController@listProductByRegion');
Route::get('products/getProductInCombo', 'ProductsController@getProductInCombo')->name('products.getProductInCombo');
Route::get('products/getSimpleProduct', 'ProductsController@getSimpleProduct')->name('products.getSimpleProduct');

Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', 'DashboardController@index');

    Route::group(['middleware' => 'acl'], function () {
        // Users
        Route::get('users/datatables', 'UsersController@getDatatables')->name('users.datatables');
        Route::resource('users', 'UsersController');
        Route::get('users/{user}/permissions', 'UserPermissionsController@index')->name('userPermissions.index');
        Route::put('users/{user}/permissions', 'UserPermissionsController@update')->name('userPermissions.update');

        // Roles
        Route::get('roles/datatables', 'RolesController@getDatatables')->name('roles.datatables');
        Route::resource('roles', 'RolesController');
        Route::get('roles/{role}/permissions', 'RolePermissionsController@index')->name('rolePermissions.index');
        Route::put('roles/{role}/permissions', 'RolePermissionsController@update')->name('rolePermissions.update');

        // Permissions
        Route::resource('permissions', 'PermissionsController', ['only' => ['index']]);

        // Categories
        Route::get('categories/listing', 'CategoriesController@listing')->name('categories.listing');
        Route::resource('categories', 'CategoriesController');
        Route::get('categories/{category}/margins', 'CategoryMarginsController@index')->name('categories.margins.index');
        Route::put('categories/{category}/margins', 'CategoryMarginsController@update')->name('categories.margins.update');
        Route::get('categories/{category}/unassigned-attributes', 'CategoryUnassignedAttributesController@index')->name('categories.unassigned-attributes.index');
        Route::post('categories/{category}/attributes/{attribute}', 'CategoryAttributesController@store')->name('categories.attributes.store');
        Route::delete('categories/{category}/attributes/{attribute}', 'CategoryAttributesController@destroy')->name('categories.attributes.destroy');

        // Attributes
        Route::get('attributes/listing', 'AttributesController@listing')->name('attributes.listing');
        Route::resource('attributes', 'AttributesController');
        Route::get('attributes/{attribute}/options', 'AttributeOptionsController@index')->name('attributes.options.index');
        Route::post('attributes/{attribute}/options', 'AttributeOptionsController@store')->name('attributes.options.store');
        Route::put('attributes/{attribute}/options/{option}', 'AttributeOptionsController@update')->name('attributes.options.update');

        // Product Attributes
        Route::put('products/{product}/attributes', 'ProductAttributesController@update');

        // Manufacturers
        Route::get('manufacturers/datatables', 'ManufacturersController@getDatatables')->name('manufacturers.datatables');
        Route::resource('manufacturers', 'ManufacturersController');

        // Colors
        Route::get('color/datatables', 'ColorsController@getDatatables')->name('colors.datatables');
        Route::resource('colors', 'ColorsController');

        // Products
        Route::get('products/listing', 'ProductsController@listing')->name('products.listing');
        Route::resource('products', 'ProductsController', ['except' => 'destroy']);

        Route::post('categories/{category}/products', 'CategoryProductsController@store')->name('categories.products.store');

        // Suppliers
        Route::get('suppliers/listing', 'SuppliersController@listing')->name('suppliers.listing');
        Route::resource('suppliers', 'SuppliersController');

        // ProductCombos
        Route::get('combo/datatables', 'ComboController@getDatatables')->name('combo.datatables');
        Route::resource('combo', 'ComboController', ['except' => 'destroy']);
        Route::post('combo/destroyProduct', 'ComboController@destroyProduct')->name('combo.destroyProduct');

        // Bundles
        Route::get('bundles/datatables', 'BundlesController@getDatatables')->name('bundles.datatables');
        Route::resource('bundles', 'BundlesController', ['except' => 'destroy']);
        Route::post('bundles/{bundle}/toggleStatus', 'BundlesController@toggleStatus')->name('bundles.status.toggle');

        // BundleCateogories
        Route::get('bundleCategories/datatables', 'BundleCategoriesController@getDatatables')->name('bundleCategories.datatables');
        Route::resource('bundleCategories', 'BundleCategoriesController', ['except' => 'destroy']);
        Route::post('bundleCategories/{bundleCategory}/toggleStatus', 'BundleCategoriesController@toggleStatus')->name('bundleCategories.status.toggle');
        Route::get('bundleProducts/{bundleCategory}/create', 'BundleProductsController@create')->name('bundleProducts.create');
        Route::put('bundleProducts/{bundleCategory}/store', 'BundleProductsController@store')->name('bundleProducts.store');

        // BundleProducts
        Route::get('bundleProducts/datatables', 'BundleProductsController@getDatatables')->name('bundleProducts.datatables');
        Route::resource('bundleProducts', 'BundleProductsController', ['except' => ['destroy','create','store']]);
        Route::post('bundleProducts/destroy', 'BundleProductsController@destroy')->name('bundleProducts.destroy');

        // For supplier
        Route::get('supplier/supplier_datatables', 'ForSupplierController@getDatatables')->name('supplier.supplier_datatables');
        Route::get('supplier/ajaxGetProductById', 'ForSupplierController@ajaxGetProductById')->name('supplier.ajaxGetProductById');
        Route::get('supplier/ajaxGetProductByName', 'ForSupplierController@ajaxGetProductByName')->name('supplier.ajaxGetProductByName');
        Route::get('supplier/updatePrice', 'ForSupplierController@updatePrice')->name('supplier.updatePrice');
        Route::post('supplier/updatePrice', 'ForSupplierController@postUpdatePrice')->name('supplier.postUpdatePrice');

        // Suppliers
        Route::get('suppliers/datatables', 'SuppliersController@getDatatables')->name('suppliers.datatables');
        Route::post('suppliers/datatables-edit', 'SuppliersController@updateDatatables')->name('suppliers.datatables-edit');
        Route::post('suppliers/getSuppliers', 'SuppliersController@getSuppliers')->name('suppliers.getSuppliers');
        Route::post('suppliers/updateStatus', 'SuppliersController@updateStatus')->name('suppliers.updateStatus');
        Route::post('suppliers/updateIdProduct', 'SuppliersController@updateIdProduct')->name('suppliers.updateIdProduct');
        Route::post('suppliers/exportExcel', 'SuppliersController@exportExcel')->name('suppliers.exportExcel');
        Route::post('suppliers/importExcel', 'SuppliersController@importExcel')->name('suppliers.importExcel');
        Route::post('suppliers/updateValidTime', 'SuppliersController@updateValidTime')->name('suppliers.updateValidTime');

        // Transport Fees
        Route::resource('transport-fees', 'TransportFeesController', ['except' => 'destroy']);

        // Margins for Orders
        Route::get('margins/datatables', 'MarginsController@getDatatables')->name('margins.datatables');
        Route::resource('margins', 'MarginsController', ['except' => 'destroy']);

        // Model Tracking Logs
        Route::get('model-tracking-logs', 'ModelTrackingLogsController@index');
        Route::get('model-tracking-logs/datatables', 'ModelTrackingLogsController@getDatatables');

        // Product Suppliers
        Route::post('product-suppliers/update-all-prices-to-magento', 'ProductSuppliersController@updateAllPricesToMagento')->name('product-suppliers.update-all-prices-to-magento');
        Route::get('product-suppliers', 'ProductSuppliersController@index')->name('product-suppliers.index');
        Route::post('product-suppliers', 'ProductSuppliersController@store')->name('product-suppliers.store');
        Route::post('product-suppliers/update-valid-time', 'ProductSuppliersController@updateValidTime')->name('product-suppliers.updateValidTime');
        Route::put('product-suppliers/{id}', 'ProductSuppliersController@update')->name('product-suppliers.update');

        // Report
        Route::get('reports/import-price', 'ReportsController@importPrice')->name('reports.importPrice');;
    });
});
