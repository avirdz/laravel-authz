<?php

namespace Avirdz\LaravelAuthz\Traits;

trait AuthzUser
{
    protected $isSuperAdminAttr;

    public function groups()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Group::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Permission::class)
            ->withPivot(['user_id', 'permission_id', 'permission_status']);
    }

    public function isSuperAdmin()
    {
        if ($this->isSuperAdminAttr === null) {
            $this->isSuperAdminAttr = false;

            if ($this->relationLoaded('groups')) {
                if (!$this->groups->isEmpty()) {
                    $this->isSuperAdminAttr = $this->groups
                        ->contains('id', \Avirdz\LaravelAuthz\Models\Group::SYS_ADMIN_ID);
                }
            } else {
                // check directly
                $this->isSuperAdminAttr = $this->groups()
                    ->where('id', \Avirdz\LaravelAuthz\Models\Group::SYS_ADMIN_ID)
                    ->selectRaw('1')
                    ->exists();
            }
        }

        return $this->isSuperAdminAttr;
    }

    public function getIsSuperAdminAttribute()
    {
        $isAdmin = false;

        if ($this->relationLoaded('groups')) {
            $isAdmin = $this->isSuperAdmin();
        }

        return $isAdmin;
    }
}
