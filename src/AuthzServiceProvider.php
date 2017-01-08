<?php

namespace Avirdz\LaravelAuthz;

use Illuminate\Support\ServiceProvider;

class AuthzServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

//        if ($this->app->runningInConsole()) {
//            $this->commands([]);
//        }

        $this->publishes([
            __DIR__ . '/../config/authz.php' => config_path('authz.php')
        ], 'config');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {

    }
}
