<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // bind import service interface to import service
        $this->app->bind('App\Services\Import\Contracts\ImportServiceInterface', 'App\Services\Import\ImportService');

        // bind user service interface to user service
        $this->app->bind('App\Services\User\Contracts\UserServiceInterface', 'App\Services\User\UserService');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
