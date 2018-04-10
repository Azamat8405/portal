<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    // public function boot()
    // {
    //     $this->registerPolicies();
    // }



    /**
    * Регистрация любых сервисов аутентификации/авторизации для приложения.
    *
    * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
    * @return void
    */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        $gate->define('ucenka-read', function ($user) {

            $roles = explode(',', $user->role);
            if(in_array('ucenka-read', $roles))
            {
                return true;
            }
            return false;
        });
    }
}
