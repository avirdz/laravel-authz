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

        // authz commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\AuthzDenyGroup::class,
                Commands\AuthzDenyUser::class,
                Commands\AuthzGrantGroup::class,
                Commands\AuthzGrantUser::class,
                Commands\AuthzGroupAddUser::class,
                Commands\AuthzGroupCreate::class,
                Commands\AuthzGroupDelete::class,
                Commands\AuthzGroupRemoveUser::class,
                Commands\AuthzGroups::class,
                Commands\AuthzGroupUsers::class,
                Commands\AuthzPermissionCreate::class,
                Commands\AuthzPermissionDelete::class,
                Commands\AuthzPermissions::class,
                Commands\AuthzPermissionSet::class,
                Commands\AuthzResourcePermissionException::class,
                Commands\AuthzShareResource::class,
                Commands\AuthzUnshareResource::class,
                Commands\AuthzUserGroups::class,
            ]);
        }

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
