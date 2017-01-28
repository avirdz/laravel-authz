<?php

namespace Avirdz\LaravelAuthz\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * Group for system administrator
     */
    const SYS_ADMIN_ID = 1;

    /**
     * Default Group for users
     */
    const UNASSIGNED_ID = 2;

    /**
     * Group for anonymous users
     */
    const ANONYMOUS_ID = 3;

    /**
     * Default group for new users, if apply.
     */
    const DEFAULT_GROUP_ID = self::UNASSIGNED_ID;

    protected $appends = ['is_default', 'is_system_group'];
    protected $fillable = ['name', 'description'];
    public $timestamps = false;


    /**
     * Delete method, system groups can't be modified
     * @return bool|null
     * @throws DefaultGroupException
     */
    public function delete()
    {
        if ($this->getIsSystemGroupAttribute()) {
            throw new DefaultGroupException();
        }

        return parent::delete();
    }


    /**
     * Default group attribute
     * @return bool
     */
    public function getIsDefaultAttribute()
    {
        return $this->id === self::DEFAULT_GROUP_ID;
    }

    /**
     * System group attribute
     * @return bool
     */
    public function getIsSystemGroupAttribute()
    {
        return in_array($this->id, [
            self::SYS_ADMIN_ID,
            self::UNASSIGNED_ID,
            self::ANONYMOUS_ID,
        ]);
    }

    /**
     * Permissions list from this group
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Permission::class)
            ->withPivot(['group_id', 'permission_id', 'permission_status']);
    }

    /**
     * Save method, system groups can't be modified
     * @param array $options
     * @return bool
     * @throws DefaultGroupException
     */
    public function save(array $options = [])
    {
        if ($this->getIsSystemGroupAttribute()) {
            throw new DefaultGroupException();
        }

        return parent::save($options);
    }

    /**
     * Users list from this group
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
