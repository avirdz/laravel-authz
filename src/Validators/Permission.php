<?php

namespace Avirdz\LaravelAuthz\Validators;

use Validator;

class Permission
{
    public static function getValidator($data)
    {
        return Validator::make($data, [
            'key_name' => 'bail|required|string|unique:permissions|max:50',
            'value' => 'required|numeric|min:-1|max:5',
            'description' => 'string|max:150',
        ]);
    }
}
