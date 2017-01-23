<?php

namespace Avirdz\LaravelAuthz;

use Gate;
use Auth;
use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class Authz
{
    protected $permissionName;
    protected $resourceName;
    protected $shareBy;
    protected $boundModel;
    protected $definedPermissions = [];


    public function __construct($permissionName, $resourceName, $sharedBy)
    {
        $this->permissionName = $permissionName;
        $this->resourceName = $resourceName;
        $this->shareBy = $sharedBy;
    }

    protected function defineAllowAll($key)
    {
        Gate::define($key, function () {
            return true;
        });

        return $this;
    }

    protected function defineByGroup(Permission $permission)
    {
        Gate::define($permission->key_name, function () use ($permission) {
            if (Auth::check()) {
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
                if (!Auth::getUser()->permissions->isEmpty()) {
                    $current = Auth::getUser()->permissions->where('id', $permission->id)->first();
                    if ($current instanceof Permission && $current->exists
                        && $current->value == Permission::GRANTED
                    ) {
                        return true;
                    }
                }
            }

            return false;
        });

        return $this;
    }


    protected function defineDenyAll($key)
    {
        // @todo admin must have access
        Gate::define($key, function () {
            return false;
        });

        return $this;
    }

    protected function defineOnlyAnonymous($key)
    {
        // @todo need to make test for guests users.
        Gate::define($key, function () {
            if (!Auth::check()) {
                return true;
            }

            return false;
        });

        return $this;
    }

    protected function defineOnlyAuthenticated($key)
    {
        Gate::define($key, function () {
            if (Auth::check()) {
                return true;
            }

            return false;
        });

        return $this;
    }

    protected function defineOnlyMe($key)
    {
        Gate::define($key, function ($user, $resource) {
            if ($user === null || $resource === null) {
                return false;
            }

            return $resource->user_id == $user->id;
        });

        return $this;
    }

    protected function defineOnlyMeShared(Permission $permission)
    {
        $sharedBy = $this->shareBy;

        Gate::define($permission->key_name, function ($user, $resource) use ($sharedBy, $permission) {
            if ($user === null || $resource === null) {
                return false;
            }

            if ($resource->user_id == $user->id) {
                return true;
            } else {
                $sharedResource = $resource;

                // load the parent resourc
                // it must be a BelongsTo relationship
                if ($sharedBy !== null) {
                    if (method_exists($resource, $sharedBy) && !$resource->relationLoaded($sharedBy)) {
                        $relationship = $resource->{$sharedBy}();

                        if ($relationship instanceof BelongsTo) {
                            $resource->load($sharedBy);
                        } else {
                            throw new \Exception('Resource parent is not a single resource');
                        }

                        $sharedResource = $resource->{$sharedBy};
                    }
                }

                // check if the resource is share with me
                if (method_exists($sharedResource, 'isSharedWithMe')) {
                    return $sharedResource->isSharedWithMe($permission->id);
                }
            }

            return false;
        });

        return $this;
    }

    public function definePermission(Permission $permission)
    {
        if (in_array($permission->id, $this->definedPermissions)) {
            return $this;
        }

        $this->definedPermissions[] = $permission->id;

        if ($permission->value == Permission::DENY_ALL) {
            return $this->defineAllowAll($permission->key_name);
        } elseif ($permission->value == Permission::ALLOW_ALL) {
            return $this->defineAllowAll($permission->key_name);
        } elseif ($permission->value == Permission::ONLY_ME) {
            return $this->defineOnlyMe($permission->key_name);
        } elseif ($permission->value == Permission::ONLY_ME_SHARED) {
            return $this->defineOnlyMeShared($permission);
        } elseif ($permission->value == Permission::ONLY_ANONYMOUS) {
            return $this->defineOnlyAnonymous($permission->key_name);
        } elseif ($permission->value == Permission::ONLY_AUTHENTICATED) {
            return $this->defineOnlyAuthenticated($permission->key_name);
        } elseif ($permission->value == Permission::CHECK_STATUS) {
            return $this->defineByGroup($permission);
        }
    }

    public function isRequestDenied(Request $request)
    {
        if ($this->resourceName !== null) {
            if ($request->route()->hasParameter($this->resourceName)) {
                $this->boundModel = $request->route($this->resourceName);
            }
        }

        // all the routes must have at least one permission
        // except when the route is public but you want to validate another permissions
        // in controllers or blade templates.
        // @todo all the routes MUST have at least one permission key
        if ($this->permissionName !== null && Gate::denies($this->permissionName, $this->boundModel)) {
            return true;
        }

        return false;
    }
}
