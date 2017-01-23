<?php

namespace Avirdz\LaravelAuthz\Traits;

trait AuthzResource
{
    protected $sharedWithMe;

    public function sharedWith()
    {
        $userClass = config('authz.user_model');

        if (!class_exists($userClass)) {
            throw new \Exception('User model doesn\'t exist: ' . $userClass);
        }

        return $this->morphToMany($userClass, 'shareable');
    }

    public function isSharedWithMe($userId, $permissionId)
    {
        if ($this->sharedWithMe === null) {
            $shareableUser = $this->sharedWith()
                ->wherePivot('user_id', $userId)
                ->select('pivot_id')
                ->first();

            if (!empty($shareableUser)) {
                $this->sharedWithMe = !\DB::table('permission_shareable')
                    ->where('shareable_id', $shareableUser->pivot->id)
                    ->where('permission_id', $permissionId)
                    ->selectRaw('1')
                    ->exists();
            } else {
                $this->sharedWithMe = false;
            }
        }

        return $this->sharedWithMe;
    }
}
