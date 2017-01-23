<?php

namespace Avirdz\LaravelAuthz\Middleware;

use Auth;
use Avirdz\LaravelAuthz\Models\Group;
use Avirdz\LaravelAuthz\Models\Permission;
use Closure;
use Avirdz\LaravelAuthz\Authz;

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

        if (Auth::check()) {
            // inject the user's permissions and groups with permissions.
            Auth::getUser()->load('permissions', 'groups.permissions');

            // if no groups, load the default group
            if (Auth::getUser()->groups->isEmpty()) {
                $defaultGroup = Group::find(Group::UNASSIGNED_ID);

                if ($defaultGroup instanceof Group) {
                    $defaultGroup->load('permissions');
                }

                Auth::getUser()->groups->push($defaultGroup);
            }

            if (!Auth::getUser()->groups->isEmpty()) {
                foreach (Auth::getUser()->groups as $group) {
                    if (!$group->permissions->isEmpty()) {
                        foreach ($group->permissions as $permission) {
                            $authz->definePermission($permission);
                        }
                    }
                }
            }

            if (!Auth::getUser()->permissions->isEmpty()) {
                foreach (Auth::getUser()->permissions as $permission) {
                    $authz->definePermission($permission);
                }
            }
        } else {
            // load permissions for anonymous group
            $permissions = Permission::join('group_permission', 'group_permission.permission_id', '=', 'permission.id')
                ->where('group_permission.group_id', Group::ANONYMOUS_ID)
                ->select('permissions.*')
                ->get();

            // define permissions on gate
            if (!$permissions->isEmpty()) {
                foreach ($permissions as $permission) {
                    $authz->definePermission($permission);
                }
            }
        }

        if ($authz->isRequestDenied($request)) {
            abort(403);
        }

        return $next($request);
    }
}
