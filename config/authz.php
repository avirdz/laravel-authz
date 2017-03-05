<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    */
    'user_model' => 'App\User',
    'group_model' => Avirdz\LaravelAuthz\Models\Group::class,
    'permission_model' => Avirdz\LaravelAuthz\Models\Permission::class,
    'shareable_model' => Avirdz\LaravelAuthz\Models\Shareable::class,

    /*
    |--------------------------------------------------------------------------
    | Cache expiration time in minutes
    |--------------------------------------------------------------------------
    |
    */
    'cache_expire' => 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Single permission mode
    |--------------------------------------------------------------------------
    | If true, it will only check for the primary permission set in the route.
    | If false, it will iterate through the entire list of permissions.
    |
    */
    'single_mode' => false,

    /*
    |--------------------------------------------------------------------------
    | [NOT IMPLEMENTED] Permission mode exceptions (dependency from single_mode config)
    |--------------------------------------------------------------------------
    | List of routes to act inversely as single_mode config
    | Example, single_mode is false but you want to threat your api routes as single mode
    | you should set something like this: api/*
    */
    'permission_mode_list' => [],

    /*
    |--------------------------------------------------------------------------
    | [NOT IMPLEMENTED] Route permissions dependencies
    |--------------------------------------------------------------------------
    | This config is optional but it loads the permissions faster.
    | Api calls or ajax request that don't need or don't use different permissions
    | are not needed to set them.
    | For example you should set the following:
    |
    */
    'route_permissions' => [],
];
