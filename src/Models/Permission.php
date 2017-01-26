<?php

namespace Avirdz\LaravelAuthz\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * Indicates that the system needs to search for custom permissions
     */
    const CHECK_STATUS = -1;
    /**
     * All groups and users have access denied.
     * Exception, system admin is allowed
     */
    const DENY_ALL = 0; //
    /**
     * All groups and users have access granted.
     */
    const ALLOW_ALL = 1;
    /**
     * Only the owner has access granted
     * System Administrator has access granted
     */
    const ONLY_ME = 2;
    /**
     * Only the owner and the users with associated permissions have access granted
     * The owner must share the content in order to allow others manage his content
     * Also system administrator has access granted
     */
    const ONLY_ME_SHARED = 3;
    /**
     * Only anonymous have access granted.
     * Unauthenticated users
     */
    const ONLY_ANONYMOUS = 4;
    /**
     * Only authenticated users from any group (except anonymous) have access granted.
     */
    const ONLY_AUTHENTICATED = 5;

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
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Shareable::class);
    }

    /**
     * Groups list from this permission
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Group::class);
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

        return $this->belongsToMany($userClass);
    }
}
