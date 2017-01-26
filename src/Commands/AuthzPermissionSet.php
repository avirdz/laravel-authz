<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzPermissionSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:permission-set {permission} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set permission configuration value';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $permissionId = $this->argument('permission');
        $value = $this->argument('value');

        $values = [
            'check' => Permission::CHECK_STATUS,
            'deny' => Permission::DENY_ALL,
            'allow' => Permission::ALLOW_ALL,
            'me' => Permission::ONLY_ME,
            'shared' => Permission::ONLY_ME_SHARED,
            'anonymous' => Permission::ONLY_ANONYMOUS,
            'auth' => Permission::ONLY_AUTHENTICATED,
        ];


        if (is_string($value) && array_key_exists($value, $values)) {
            $value = $values[$value];
        } elseif (is_numeric($value) && in_array($value, $values)) {
            $value = (int) $value;
        } else {
            $this->error('Invalid value');
            exit(1);
        }

        if (is_numeric($permissionId) && $permissionId > 0) {
            $permission = Permission::find($permissionId);
        } elseif (is_string($permissionId) && strlen($permissionId)) {
            $permission = Permission::where('key_name', $permissionId)->limit(1)->first();
        }

        if (!isset($permission) || !$permission instanceof Permission) {
            $this->error('Permission not found');
            exit(1);
        }

        $permission->value = $value;
        $permission->save();

        // @todo set names to the values on permission model
        $this->info($permission->key_name . ' set to ' . $permission->value);

        // remove group and users permissions
        if ($permission->value !== Permission::CHECK_STATUS) {
            $answer = $this->ask('Do you want to clear groups and users from pivot tables? (y/n)', 'n');

            if (strtolower($answer) === 'y') {
                $results = $permission->groups()->detach();
                $this->info($results . ' groups have been removed from the pivot table');

                $results = $permission->users()->detach();
                $this->info($results . ' users have been removed from the pivot table');
            }
        }
    }
}
