<?php

namespace Avirdz\LaravelAuthz\Middleware;

use Auth;
use Avirdz\LaravelAuthz\Models\Permission;
use Closure;
use Gate;
use Illuminate\Support\Facades\Cache;

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
        if (Auth::check()) {
            // inject the user's permissions and groups with permissions.
            Auth::getUser()->load('permissions', 'groups.permissions');
        }

        // full list of permissions
        $permissions = Permission::all();


        if (!$permissions->isEmpty()) {
            foreach ($permissions as $permission) {
                if ($permission->value == Permission::DENY_ALL) {
                    Gate::define($permission->key_name, function () {
                        return false;
                    });
                } elseif ($permission->value == Permission::ALLOW_ALL) {
                    Gate::define($permission->key_name, function () {
                        return true;
                    });
                } elseif ($permission->value == Permission::ONLY_ME) {
                    Gate::define($permission->key_name, function ($user, $resource) {
                        if ($resource->user_id == $user->id) {
                            return true;
                        }

                        return false;
                    });
                } elseif ($permission->value == Permission::ONLY_ME_SHARED) {
                    Gate::define($permission->key_name, function ($user, $resource) use ($sharedBy) {
                        if (is_null($resource)) {
                            return false;
                        }

                        if ($resource->user_id == $user->id) {
                            return true;
                        } else {
                            $sharedResource = $resource;

                            // load the shared resource
                            if (!empty($sharedBy)) {
                                if (method_exists($resource, $sharedBy)) {
                                    if (!$resource->relationLoaded($sharedBy)) {
                                        $resource->load($sharedBy);
                                        $sharedResource = $resource->{$sharedBy}()->get();
                                    }
                                }
                            }

                            // shared resource checked by users relationship (always)
                            if (method_exists($sharedResource, 'users')) {
                                return $sharedResource->users()
                                    ->where('id', $user->id)
                                    ->selectRaw('1')
                                    ->exists();
                            }
                        }

                        return false;
                    });
                } elseif ($permission->value == Permission::ONLY_ANONYMOUS) {
                    //need to make test for guests users.
                    Gate::define($permission->key_name, function () {
                        if (!Auth::check()) {
                            return true;
                        }

                        return false;
                    });
                } elseif ($permission->value == Permission::ONLY_AUTHENTICATED) {
                    Gate::define($permission->key_name, function () {
                        if (Auth::check()) {
                            return true;
                        }

                        return false;
                    });
                } elseif ($permission->value == Permission::CHECK_STATUS) {
                    Gate::define($permission->key_name, function () use ($permission) {
                        if (Auth::check()) {
                            // check by group
                            if (!Auth::getUser()->relationLoaded('groups')) {
                                Auth::getUser()->load('groups.permissions');
                            }

                            if (!Auth::getUser()->groups->isEmpty()) {
                                foreach (Auth::getUser()->groups as $group) {
                                    $current = $group->permissions->where('id', $permission->id)->first();

                                    if ($current instanceof Permission && $current->exists
                                        && $current->value == Permission::GRANTED) {
                                        return true;
                                    }
                                }
                            }

                            // group doesn't have a permission but, maybe there is an exception for
                            // the current user
                            if (!Auth::getUser()->relationLoaded('permissions')) {
                                Auth::getUser()->load('permissions');
                            }

                            $current = Auth::getUser()->permissions->where('id', $permission->id)->first();
                            if ($current instanceof Permission && $current->exists
                                && $current->value == Permission::GRANTED) {
                                return true;
                            }
                        }

                        return false;
                    });
                }
            }
        }

        $boundModel = null;
        if (!is_null($resourceName)) {
            if ($request->route()->hasParameter($resourceName)) {
                $boundModel = $request->route($resourceName);
            }
        }

        // all the routes must have at least one permission
        // except when the route is public but you want to validate another permissions
        // in controllers or blade templates.
        // @todo all the routes MUST have at least one permission key
        if (!is_null($permissionName) && Gate::denies($permissionName, $boundModel)) {
            abort(403);
        }

        return $next($request);
    }
}
