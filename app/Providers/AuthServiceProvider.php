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

    private function checkRole($user, $rol)
    {
        $roles = explode(',', $user->role);

        if(in_array('admin', $roles))
        {
            return true;
        }

        if(in_array($rol, $roles))
        {
            return true;
        }
        return false;
    }

    /**
    * Регистрация любых сервисов аутентификации/авторизации для приложения.
    *
    * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
    * @return void
    */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        $gate->define('admin', function ($user) {
            return $this->checkRole($user, 'admin');
        });

        $gate->define('ucenkaapp-read', function ($user) {
            return $this->checkRole($user, 'ucenkaapp-read');
        });

        $gate->define('ucenkaapp-edit', function ($user) {
            return $this->checkRole($user, 'ucenkaapp-edit');
        });

        $gate->define('ucenkaapp-create', function ($user) {
            return $this->checkRole($user, 'ucenkaapp-create');
        });
    }
}