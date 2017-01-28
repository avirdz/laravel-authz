<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Group;
use Illuminate\Console\Command;

class AuthzPermissionView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:permission-view {id} {--type=group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List of permissions from a user or group';

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
        $id = $this->argument('id');
        $type = $this->option('type');

        if ($type === 'group') {
            if (is_numeric($id) && $id > 0) {
                $group = Group::find($id);
            } elseif (is_string($id)) {
                $group = Group::where('name', $id)->limit(1)->first();
            }

            if (!isset($group) || !$group instanceof Group) {
                $this->error('Group not found');
                exit(1);
            }

            $permissions = $group->permissions()
                ->select(['id', 'key_name', 'value', 'permission_status'])
                ->get();

            if ($permissions->isEmpty()) {
                $this->info('No permissions');
            } else {
                $output = $permissions->makeHidden('pivot')->toArray();
                $this->table([
                    'Id',
                    'Key name',
                    'Value',
                    'Exception',
                ], $output);
            }
        } elseif ($type === 'user') {
            $userClass = config('authz.user_model');

            if (is_numeric($id) && $id > 0) {
                $user = $userClass::find($id);
            } elseif (is_string($id)) {
                $user = $userClass::where('email', $id)->limit(1)->first();
            }

            if (!isset($user) || !$user instanceof $userClass) {
                $this->error('User not found');
                exit(1);
            }

            $permissions = $user->permissions()
                ->select(['id', 'key_name', 'value', 'permission_status'])
                ->get();

            if ($permissions->isEmpty()) {
                $this->info('No permissions');
            } else {
                $output = $permissions->makeHidden('pivot')->toArray();
                $this->table([
                    'Id',
                    'Key name',
                    'Value',
                    'Exception',
                ], $output);
            }
        }


    }
}
