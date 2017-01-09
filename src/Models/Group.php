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


    const DEFAULT_GROUP_ID = self::UNASSIGNED_ID;


    protected $appends = ['is_default', 'is_system_group'];
    protected $fillable = ['name', 'description'];
    public $timestamps = false;


    public function delete()
    {
        if (in_array($this->id, [
            self::SYS_ADMIN_ID,
            self::UNASSIGNED_ID,
            self::ANONYMOUS_ID,
        ])) {
            throw new \Avirdz\LaravelAuthz\Models\DefaultGroupException('Cannot delete a default system group');
        }

        return parent::delete();
    }


    public function getIsDefaultAttribute()
    {
        return $this->id === self::DEFAULT_GROUP_ID;
    }

    public function getIsSystemGroupAttribute()
    {
        return in_array($this->id, [
            self::SYS_ADMIN_ID,
            self::UNASSIGNED_ID,
            self::ANONYMOUS_ID,
        ]);
    }

    public function permissions()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Permission::class);
    }

    public function save(array $options = [])
    {
        if (in_array($this->id, [
            self::SYS_ADMIN_ID,
            self::UNASSIGNED_ID,
            self::ANONYMOUS_ID,
        ])) {
            throw new \Avirdz\LaravelAuthz\Models\DefaultGroupException('Cannot update a default system group');
        }

        return parent::save($options);
    }

    public function users()
    {
        $userClass = config('authz.user_model');

        if (!class_exists($userClass)) {
            throw new \Exception('User model doesn\'t exist: ' . $userClass);
        }

        return $this->belongsToMany($userClass);
    }
}
