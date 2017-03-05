<?php

namespace Avirdz\LaravelAuthz\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * Indicates that the system needs to search for custom set permissions
     */
    const CUSTOM = -1;
    /**
     * All groups and users have access denied.
     * Exception, system admin is allowed
     */
    const ROOT = 0; //
    /**
     * All groups and users have access granted.
     */
    const ANY = 1;
    /**
     * Only the owner has access granted
     * System Administrator has access granted
     */
    const OWNER = 2;
    /**
     * Only the owner and the users with associated permissions have access granted
     * The owner must share the content in order to allow others manage his content
     * Also system administrator has access granted
     */
    const SHARED = 3;
    /**
     * Only anonymous have access granted.
     * Unauthenticated users
     */
    const ANONYMOUS = 4;
    /**
     * Only authenticated users from any group (except anonymous) have access granted.
     */
    const AUTHENTICATED = 5;

    const DENIED = 0;
    const GRANTED = 1;

    protected $fillable = [
        'key_name',
        'description',
        'value'
    ];

    protected static $default_permissions = [
        'groups.view',
        'groups.create',
        'groups.update',
        'groups.delete',
        'groups.add-user',
        'groups.remove-user',
        'permissions.view',
        'permissions.create',
        'permissions.update',
        'permissions.delete',
        'permissions.grant',
        'permissions.deny',
        'shareables.share',
        'shareables.unshare',
        'shareables.deny',
    ];

    public $timestamps = false;

    /**
     * Delete method, system permissions can't be deleted
     * @return bool|null
     * @throws DefaultPermissionException
     */
    public function delete()
    {
        if ($this->getIsSystemPermissionAttribute()) {
            throw new DefaultPermissionException();
        }

        return parent::delete();
    }

    public function getIsSystemPermissionAttribute()
    {
        return in_array($this->key_name, self::$default_permissions);
    }

    public function shareableExceptions()
    {
        $shareableClass = config('authz.shareable_model');

        return $this->belongsToMany($shareableClass);
    }

    /**
     * Groups list from this permission
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        $groupClass = config('authz.group_model');

        return $this->belongsToMany($groupClass)
            ->withPivot(['group_id', 'permission_id', 'permission_status']);
    }

    /**
     * Save method, system permissions can't be modified
     * @param array $options
     * @return bool
     * @throws DefaultPermissionException
     */
    public function save(array $options = [])
    {
        if ($this->getIsSystemPermissionAttribute()) {
            throw new DefaultPermissionException();
        }

        return parent::save($options);
    }

    /**
     * Users list from this permission
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * @throws \Exception
     */
    public function users()
    {
        $userClass = config('authz.user_model');

        if (!class_exists($userClass)) {
            throw new InvalidUserModelException();
        }

        return $this->belongsToMany($userClass)
            ->withPivot(['user_id', 'permission_id', 'permission_status']);
    }
}
