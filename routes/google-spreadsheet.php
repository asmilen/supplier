<?php

Route::post('products', 'CreateProducts');

Route::put('product-supplier/prices', 'ProductSupplierPricesController@update');
