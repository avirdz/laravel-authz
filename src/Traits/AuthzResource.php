<?php

namespace Avirdz\LaravelAuthz\Traits;

trait AuthzResource
{
    public function sharedWith()
    {
        $userClass = config('authz.user_model');

        if (!class_exists($userClass)) {
            throw new \Exception('User model doesn\'t exist: ' . $userClass);
        }

        return $this->morphToMany($userClass, 'shareable');
    }
}
