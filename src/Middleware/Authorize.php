<?php

namespace Avirdz\LaravelAuthz\Middleware;

use Auth;
use Cache;
use Closure;
use Avirdz\LaravelAuthz\Authz;
use Avirdz\LaravelAuthz\Models\Group;
use Avirdz\LaravelAuthz\Models\Permission;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param string|null $permissionName
     * @param string|null $resourceName
     * @param string|null $sharedBy
     * @return mixed
     */
    public function handle($request, Closure $next, $permissionName = null, $resourceName = null, $sharedBy = null)
    {
        $authz = new Authz($permissionName, $resourceName, $sharedBy);

        // if single mode is on and no permission to check, the request is allowed
        if ($permissionName === null && $authz->isSingleModeOn()) {
            return $next($request);
        }

        // set permissions for a logged user
        if (Auth::check()) {
            // load user's groups
            Auth::getUser()->load('groups');

            // admin are allowed for all resources, so no necessity to define permissions
            if (Auth::getUser()->isSuperAdmin()) {
                return $next($request);
            }

            // if the user does not have groups, load the default group
            if (Auth::getUser()->groups->isEmpty()) {
                $defaultGroup = Group::find(Group::DEFAULT_GROUP_ID);

                if ($defaultGroup instanceof Group) {
                    Auth::getUser()->groups->push($defaultGroup);
                }
            }

            // load single permission if single mode on
            if ($authz->isSingleModeOn()) {
                $currentPermission = Permission::where('key_name', $permissionName)
                    ->select(['id', 'key_name', 'value'])
                    ->first();

                // if permission doesn't exist deny the request
                if ($currentPermission === null) {
                    abort(403);
                }
            } else {
                // load logged user relevant permissions
                $permissions = Cache::remember('logged_permissions', config('authz.cache_expire', 60), function () {
                    return Permission::whereIn('value', [
                        Permission::ANY,
                        Permission::AUTHENTICATED,
                        Permission::OWNER,
                        Permission::SHARED
                    ])->select(['id', 'key_name', 'value'])
                        ->get();
                });

                $currentPermission = null;
                if (!$permissions->isEmpty()) {
                    $currentPermission = $permissions->where('key_name', $permissionName)->first();
                }
            }

            // define the current key and test it
            if ($permissionName !== null && $currentPermission !== null) {
                $authz->definePermission($currentPermission);

                if ($authz->isRequestDenied($request)) {
                    abort(403);
                } elseif ($authz->isSingleModeOn()) {
                    return $next($request);
                }
            }


            // defining permissions by group
            if (!Auth::getUser()->groups->isEmpty()) {
                Auth::getUser()->groups->load('permissions');

                foreach (Auth::getUser()->groups as $group) {
                    if (!$group->permissions->isEmpty()) {
                        foreach ($group->permissions as $permission) {
                            $authz->definePermission($permission);
                        }
                    }
                }
            }

            // defining permissions by user exceptions
            if (!Auth::getUser()->permissions->isEmpty()) {
                foreach (Auth::getUser()->permissions as $permission) {
                    $authz->definePermission($permission);
                }
            }

            // defining global permissions
            if (isset($permissions) && !$permissions->isEmpty()) {
                foreach ($permissions as $permission) {
                    $authz->definePermission($permission);
                }
            }
        } else {
            if ($authz->isSingleModeOn()) {
                $currentPermission = Permission::where('key_name', $permissionName)
                    ->select(['id', 'key_name', 'value'])
                    ->first();

                // if permission doesn't exist deny the request
                if ($currentPermission === null) {
                    abort(403);
                } else {
                    $authz->definePermission($currentPermission);
                }
            } else {
                // load permissions for anonymous group, and permissions with value ALLOW_ALL and ONLY_ANONYMOUS
                $permissions = Cache::remember('anonymous_permissions', config('authz.cache_expire', 60), function () {
                    return Permission::join(
                        'group_permission',
                        'group_permission.permission_id',
                        '=',
                        'permissions.id'
                    )->where('group_permission.group_id', Group::ANONYMOUS_ID)
                        ->orWhereIn('permissions.value', [Permission::ANY, Permission::ANONYMOUS])
                        ->select('permissions.*')
                        ->get();
                });

                // define permissions on gate
                if (!$permissions->isEmpty()) {
                    foreach ($permissions as $permission) {
                        $authz->definePermission($permission);
                    }
                }
            }
        }

        if ($authz->isRequestDenied($request)) {
            abort(403);
        }

        return $next($request);
    }
}
