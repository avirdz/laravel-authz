<?php

namespace Avirdz\LaravelAuthz\Validators;

use Validator;

class Group
{
    public static function getValidator($data)
    {
        return Validator::make($data, [
            'name' => 'bail|required|string|unique:groups|max:30',
            'description' => 'string|max:150',
        ]);
    }
}
