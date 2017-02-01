<?php

namespace Avirdz\LaravelAuthz\Traits;

use Avirdz\LaravelAuthz\Models\InvalidUserModelException;

trait AuthzResource
{
    protected $sharedWithMe = [];

    /**
     * Shared users list from this resource
     * @return mixed
     * @throws \Exception
     */
    public function sharedWith()
    {
        $userClass = config('authz.user_model');

        if (!class_exists($userClass)) {
            throw new InvalidUserModelException();
        }

        return $this->morphedByMany($userClass, 'shareable')
            ->withPivot(['id']);
    }

    /**
     * Determines if a resource is shared with the current user
     * @param $permissionId int
     * @return bool
     */
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
