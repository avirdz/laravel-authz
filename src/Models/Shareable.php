<?php

namespace Avirdz\LaravelAuthz\Models;

use Illuminate\Database\Eloquent\Model;

class Shareable extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'shareable_id',
        'shareable_type',
    ];

    /**
     * Permission exceptions for shared resources
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissionExceptions()
    {
        return $this->belongsToMany(\Avirdz\LaravelAuthz\Models\Permission::class);
    }
}
