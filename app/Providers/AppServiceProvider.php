<?php

namespace App\Providers;

use Validator;
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
        Validator::extend('date1', function($attribute, $value, $parameters) {
            return (bool) preg_match( "/^[0-9]{1,2}(\.|\-)[0-9]{1,2}(\.|\-)[0-9]{4}$/", $value );
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
