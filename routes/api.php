<?php

Route::get('categories', 'CategoriesController@index');
Route::get('suppliers', 'SuppliersController@index');

Route::group(['middleware' => 'auth:api'], function () {
    //
});
