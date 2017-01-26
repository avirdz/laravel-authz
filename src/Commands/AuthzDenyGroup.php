<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Group;
use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzDenyGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:deny-group {permission} {group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set permission denied to a group';

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
        $groupId = $this->argument('group');

        if (is_numeric($groupId) && $groupId > 0) {
            $group = Group::find($groupId);
        } elseif (is_string($groupId) && strlen($groupId)) {
            $group = Group::where('name', $groupId)->limit(1)->first();
        }

        if (!isset($group) || !$group instanceof Group) {
            $this->error('Group not found');
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

        $permission->groups()->updateExistingPivot($group->id, ['permission_status' => Permission::DENIED]);
        $this->info($permission->key_name . ' denied to ' . $group->name);
    }
}
