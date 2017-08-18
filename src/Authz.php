<?php

namespace Avirdz\LaravelAuthz;

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

        Gate::before(function ($user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });
    }

    /**
     * Define a public permission
     * @param $permission Permission
     * @return $this
     */
    protected function defineAnyPermission(Permission $permission)
    {
        Gate::define($permission->key_name, function () {
            return true;
        });

        return $this;
    }

    /**
     * Define a permission for group and user exceptions
     * @param $permission Permission
     * @return $this
     */
    protected function defineCustomPermission(Permission $permission)
    {
        Gate::define($permission->key_name, function ($user) use ($permission) {
            $status = false;

            if ($user !== null) {
                if (!$user->groups->isEmpty()) {
                    foreach ($user->groups as $group) {
                        $current = $group->permissions->where('id', $permission->id)->first();

                        if ($current instanceof Permission && $current->exists
                            && $current->pivot->permission_status == Permission::GRANTED) {
                            $status = true;
                            break;
                        }
                    }
                }

                // maybe there is an exception for the current user
                if (!$user->permissions->isEmpty()) {
                    $current = $user->permissions->where('id', $permission->id)->first();
                    if ($current instanceof Permission && $current->exists) {
                        $status = $current->pivot->permission_status == Permission::GRANTED;
                    }
                }
            }

            return $status;
        });

        return $this;
    }


    /**
     * Define a permission for System administrator
     * @param $permission Permission
     * @return $this
     */
    protected function defineRootPermission(Permission $permission)
    {
        Gate::define($permission->key_name, function ($user) {
            return $user !== null && $user->isSuperAdmin();
        });

        return $this;
    }

    /**
     * Define a permission for anonymous users
     * @param $permission Permission
     * @return $this
     */
    protected function defineAnonymousPermission(Permission $permission)
    {
        // @todo need to make test for guests users.
        Gate::define($permission->key_name, function ($user) {
            return $user === null;
        });

        return $this;
    }

    /**
     * Define a permission for authenticated users
     * @param $permission Permission
     * @return $this
     */
    protected function defineAuthenticatedPermission(Permission $permission)
    {
        Gate::define($permission->key_name, function ($user) {
            return $user !== null;
        });

        return $this;
    }

    /**
     * Define a permission for resource owner
     * @param $permission Permission
     * @return $this
     */
    protected function defineOwnerPermission(Permission $permission)
    {
        Gate::define($permission->key_name, function ($user, $resource) {
            if ($user === null || $resource === null) {
                return false;
            }

            $resourceType = config('authz.user_model');
            if ($resource instanceof $resourceType) {
                return $resource->id == $user->id;
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
    protected function defineSharedPermission(Permission $permission)
    {
        $sharedBy = $this->sharedBy;

        Gate::define($permission->key_name, function ($user, $resource) use ($sharedBy, $permission) {
            if ($user === null || $resource === null) {
                return false;
            }

            if (isset($resource->user_id) && $resource->user_id == $user->id) {
                return true;
            } else {
                $sharedResource = $resource;

                // load the parent resource
                // it must be a BelongsTo relationship
                if ($sharedBy !== null) {
                    if (is_callable([$resource, $sharedBy])) {
                        $relationship = $resource->{$sharedBy}();

                        if (!$relationship instanceof BelongsTo) {
                            throw new \Exception('Resource parent is not a single resource');
                        }

                        $sharedResource = $resource->{$sharedBy};

                        // check if the current user is the owner of the parent resource
                        if (isset($sharedResource->user_id) && $sharedResource->user_id == $user->id) {
                            return true;
                        }
                    }
                }

                // check if the resource is shared wit the current user
                if (is_callable([$sharedResource, 'isSharedWithMe'])) {
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
        if (in_array($permission->key_name, $this->definedPermissions)) {
            return $this;
        }

        $this->definedPermissions[] = $permission->key_name;

        switch ($permission->value) {
            case Permission::CUSTOM:
                return $this->defineCustomPermission($permission);
                break;
            case Permission::ANY:
                return $this->defineAnyPermission($permission);
                break;
            case Permission::OWNER:
                return $this->defineOwnerPermission($permission);
                break;
            case Permission::SHARED:
                return $this->defineSharedPermission($permission);
                break;
            case Permission::ANONYMOUS:
                return $this->defineAnonymousPermission($permission);
                break;
            case Permission::AUTHENTICATED:
                return $this->defineAuthenticatedPermission($permission);
                break;
            case Permission::ROOT:
                return $this->defineRootPermission($permission);
                break;
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

    public function isSingleModeOn()
    {
        return config('authz.single_mode');
    }
}
