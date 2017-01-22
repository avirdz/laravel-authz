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

    public $timestamps = false;

    public function sharebleExceptions()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Shareable::class);
    }
}
