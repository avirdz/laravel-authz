<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzDenyUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:deny-user {permission} {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set permission denied to a user';

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
        $userId = $this->argument('user');

        if (is_numeric($permissionId) && $permissionId > 0) {
            $permission = Permission::find($permissionId);
        } elseif (is_string($permissionId) && strlen($permissionId)) {
            $permission = Permission::where('key_name', $permissionId)->limit(1)->first();
        }

        if (!isset($permission) || !$permission instanceof Permission) {
            $this->error('Permission not found');
            exit(1);
        }

        $userClass = config('authz.user_model');

        if (is_numeric($userId) && $userId > 0) {
            $user = $userClass::find($userId);
        } elseif (is_string($userId) && strlen($userId)) {
            $user = $userClass::where('email', $userId)->limit(1)->first();
        }

        if (!isset($user) || !$user instanceof $userClass) {
            $this->error('User not found');
            exit(1);
        }

        $permission->users()->updateExistingPivot($user->id, ['permission_status' => Permission::DENIED]);
        $this->info($permission->key_name . ' denied to ' . (isset($user->email) ? $user->email : $user->id));
    }
}
