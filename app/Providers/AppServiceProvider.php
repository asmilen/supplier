<?php

namespace App\Providers;

use Hash;
use Sentinel;
use Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('passcheck', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, Sentinel::getUser()->password);
        });

        Validator::extend('image_base64', function ($attribute, $value, $parameters, $validator) {
            return $value['filesize'] < 2000000 && ($value['filetype'] == 'image/png' || $value['filetype'] == 'image/jpeg');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }
    }
}
