<?php

namespace Avirdz\LaravelAuthz\Models;

use Exception;

class DefaultPermissionException extends Exception
{
    protected $message = 'System permission cannot be modify or deleted';
}
