<?php

namespace Avirdz\LaravelAuthz\Traits;

trait AuthzUser
{
    public function groups()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Group::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Permission::class);
    }
}
