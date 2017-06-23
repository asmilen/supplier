<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Laravel\Dusk\DuskServiceProvider as BaseDuskServiceProvider;

class DuskServiceProvider extends BaseDuskServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        Route::get('/_dusk/login/{userId}/{guard?}', [
            'middleware' => 'web',
            'uses' => 'App\Http\Controllers\Dusk\UserController@login',
        ]);

        Route::get('/_dusk/logout/{guard?}', [
            'middleware' => 'web',
            'uses' => 'App\Http\Controllers\Dusk\UserController@logout',
        ]);

        Route::get('/_dusk/user/{guard?}', [
            'middleware' => 'web',
            'uses' => 'App\Http\Controllers\Dusk\UserController@user',
        ]);
    }
}
