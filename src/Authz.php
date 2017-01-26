<?php

namespace Avirdz\LaravelAuthz;

use Auth;
use Gate;
use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class Authz
{
    /**
     * The key name of the permission
     * @var string|null
     */
    protected $permissionName;
    /**
     * The name of the requested resource (route param name)
     * @var string|null
     */
    protected $resourceName;
    /**
     * Resource's parent
     * Some resources are not shareables but they have a direct relationship
     * to another resource. Ex. folders and files, you can share only the folder but the files
     * if a file has the permission configured as SHARED but you don't want to share every file,
     * you can share the entire folder to a user, that user will have access to every file on the shared folder.
     * An example in routes: middleware => authz:files.read,file,folder
     * @var string|null
     */
    protected $sharedBy;
    /**
     * The injected model via binder
     * @var mixed|null
     */
    protected $boundModel;
    /**
     * Avoid to define a permission already defined
     * @var array
     */
    protected $definedPermissions = [];


    /**
     * Authz constructor.
     * @param $permissionName string|null
     * @param $resourceName string|null
     * @param $sharedBy string|null
     */
    public function __construct($permissionName, $resourceName, $sharedBy)
    {
        $this->permissionName = $permissionName;
        $this->resourceName = $resourceName;
        $this->sharedBy = $sharedBy;
    }

    /**
     * Define a public permission
     * @param $key string
     * @return $this
     */
    protected function defineAllowAll($key)
    {
        Gate::define($key, function () {
            return true;
        });

        return $this;
    }

    /**
     * Define a permission for group and user exceptions
     * @param Permission $permission
     * @return $this
     */
    protected function defineByGroup(Permission $permission)
    {
        Gate::define($permission->key_name, function () use ($permission) {
            $status = false;
            if (Auth::check()) {
                if (!Auth::getUser()->groups->isEmpty()) {
                    foreach (Auth::getUser()->groups as $group) {
                        $current = $group->permissions->where('id', $permission->id)->first();

                        if ($current instanceof Permission && $current->exists
                            && $current->value == Permission::GRANTED) {
                            $status = true;
                            break;
                        }
                    }
                }

                // maybe there is an exception for the current user
                if (!Auth::getUser()->permissions->isEmpty()) {
                    $current = Auth::getUser()->permissions->where('id', $permission->id)->first();
                    if ($current instanceof Permission && $current->exists) {
                        $status = $current->value == Permission::GRANTED;
                    }
                }
            }

            return $status;
        });

        return $this;
    }


    /**
     * Define a permission for System administrator
     * @param $key string
     * @return $this
     */
    protected function defineDenyAll($key)
    {
        // @todo admin must have access
        Gate::define($key, function () {
            return false;
        });

        return $this;
    }

    /**
     * Define a permission for anonymous users
     * @param $key string
     * @return $this
     */
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

    /**
     * Define a permission for authenticated users
     * @param $key
     * @return $this
     */
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

    /**
     * Define a permission for resource owner
     * @param $key string
     * @return $this
     */
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

    /**
     * Define a permission for resource owner and shared users
     * @param Permission $permission
     * @return $this
     */
    protected function defineOnlyMeShared(Permission $permission)
    {
        $sharedBy = $this->sharedBy;

        Gate::define($permission->key_name, function ($user, $resource) use ($sharedBy, $permission) {
            if ($user === null || $resource === null) {
                return false;
            }

            if ($resource->user_id == $user->id) {
                return true;
            } else {
                $sharedResource = $resource;

                // load the parent resource
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

    /**
     * Define a permission into the Gate
     * @param Permission $permission
     * @return $this|Authz
     */
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

        return $this;
    }

    /**
     * Checks if the request is valid
     * @param Request $request Current request
     * @return bool True is it is denied, false otherwise.
     */
    public function isRequestDenied(Request $request)
    {
        if ($this->resourceName !== null) {
            if ($request->route()->hasParameter($this->resourceName)) {
                $this->boundModel = $request->route($this->resourceName);
            }
        }

        return $this->permissionName !== null && Gate::denies($this->permissionName, $this->boundModel);
    }
}
