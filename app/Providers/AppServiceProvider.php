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
        Validator::extend('start_action_date', function($attribute, $value, $parameters) {

            $valid = (bool) preg_match("/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
            if($valid)
            {
                $valid = ($parameters[0] <= strtotime($value));
            }
            return $valid;
        });

        Validator::extend('end_action_date', function($attribute, $value, $parameters) {
            $valid = (bool) preg_match( "/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$/", $value);
            if($valid)
            {
                $valid = ($parameters[0] >= strtotime($value));
            }
            return $valid;
        });
        Validator::extend('procent', function($attribute, $value, $parameters) {

            if(trim($value) != '')
            {
                if(floatval($value) < 100 && floatval($value) > 0)
                {
                    return true;
                }
            }
            return false;
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
