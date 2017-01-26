<?php

namespace Avirdz\LaravelAuthz\Models;

use Exception;

class DefaultGroupException extends Exception
{
    protected $message = 'System group cannot be modify or deleted';
}
