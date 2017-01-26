<?php

namespace Avirdz\LaravelAuthz\Models;

use Exception;

class InvalidUserModelException extends Exception
{
    protected $message = 'User model class doesn\'t exist';
}
