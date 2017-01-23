<?php

namespace Avirdz\LaravelAuthz\Traits;

trait AuthzResource
{
    protected $sharedWithMe = [];

    public function sharedWith()
    {
        $userClass = config('authz.user_model');

        if (!class_exists($userClass)) {
            throw new \Exception('User model doesn\'t exist: ' . $userClass);
        }

        return $this->morphToMany($userClass, 'shareable');
    }

    public function isSharedWithMe($permissionId)
    {
        if (!array_key_exists($permissionId, $this->sharedWithMe)) {
            $shareableInfo = \DB::table('shareables')
                ->leftJoin('permission_shareable', function ($query) use ($permissionId) {
                    $query->on('permission_shareable.shareable_id', '=', 'shareables.id');
                    $query->where('permission_shareable.permission_id', $permissionId);
                })
                ->where('shareables.user_id', \Auth::id())
                ->where('shareables.shareable_id', $this->id)
                ->where('shareables.shareable_type', $this->getActualClassNameForMorph(__CLASS__))
                ->select(['shareables.*', 'permission_shareable.shareable_id as exception'])
                ->first();

            $this->sharedWithMe[$permissionId] = false;

            if ($shareableInfo !== null) {
                $this->sharedWithMe[$permissionId] = $shareableInfo->exception === null;
            }
        }

        return $this->sharedWithMe[$permissionId];
    }
}
