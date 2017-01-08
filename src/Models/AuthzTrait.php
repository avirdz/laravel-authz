<?php

namespace Avirdz\LaravelAuthz\Models;

trait AuthzTrait
{
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
