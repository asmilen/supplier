<?php

Route::get('/', function () {
    return redirect('/dashboard');
});

Auth::routes();
Route::get('auth/google', 'Auth\AuthController@redirectToProvider');
Route::get('auth/google/callback', 'Auth\AuthController@handleProviderCallback');

Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', 'DashboardController@index');

    Route::get('profile', 'ProfileController@edit');
    Route::put('profile', 'ProfileController@update');
    Route::get('profile/password', 'ProfileController@editPassword');
    Route::put('profile/password', 'ProfileController@updatePassword');

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
        Route::get('categories/datatables', 'CategoriesController@getDatatables')->name('categories.datatables');
        Route::resource('categories', 'CategoriesController');

        // Manufacturers
        Route::get('manufacturers/datatables', 'ManufacturersController@getDatatables')->name('manufacturers.datatables');
        Route::resource('manufacturers', 'ManufacturersController');

        // Products
        Route::get('products/datatables', 'ProductsController@getDatatables')->name('products.datatables');
        Route::resource('products', 'ProductsController');

        //suppliers
        Route::get('suppliers/datatables', 'SuppliersController@getDatatables')->name('suppliers.datatables');
        Route::post('suppliers/datatables_edit', 'SuppliersController@postDatatables')->name('suppliers.datatables_edit');
        Route::resource('suppliers', 'SuppliersController');
    });
});
