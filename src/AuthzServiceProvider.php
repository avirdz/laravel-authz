<?php

namespace Avirdz\LaravelAuthz;

use Cache;
use Gate;
use Avirdz\LaravelAuthz\Models\Permission;
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
        // authz commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\AuthzDenyGroup::class,
                Commands\AuthzDenyShared::class,
                Commands\AuthzDenyUser::class,
                Commands\AuthzGrantGroup::class,
                Commands\AuthzGrantShared::class,
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
                Commands\AuthzPermissionView::class,
                Commands\AuthzShare::class,
                Commands\AuthzShareable::class,
                Commands\AuthzUnshare::class,
                Commands\AuthzUserGroups::class,
            ]);
        }
	
	if (method_exists($this, 'loadMigrationsFrom')) {
	    $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        } else {
            $this->publishes([
               __DIR__.'/../migrations/' => database_path('migrations')
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . '/../config/authz.php' => config_path('authz.php')
        ], 'config');

        Permission::saved(function ($permission) {
            \Log::debug('permission saved');
            if (Cache::has('logged_permissions')) {
                Cache::forget('logged_permissions');
                \Log::debug('logged permissions deleted');
            }

            if (Cache::has('anonymous_permissions')) {
                Cache::forget('anonymous_permissions');
                \Log::debug('anonymous permissions deleted');
            }
        });

        Gate::before(function ($user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });
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
