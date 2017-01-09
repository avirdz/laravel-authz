<?php

namespace Avirdz\LaravelAuthz\Models;

trait AuthzTrait
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
