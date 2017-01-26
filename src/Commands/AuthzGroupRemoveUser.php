<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Group;
use Illuminate\Console\Command;

class AuthzGroupRemoveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:remove-user {group} {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a user from a group';

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
        $groupId = $this->argument('group');
        $userId = $this->argument('user');

        if (is_numeric($groupId) && $groupId > 0) {
            $group = Group::find($groupId);
        } elseif (is_string($groupId) && strlen($groupId)) {
            $group = Group::where('name', $groupId)->limit(1)->first();
        }

        if (!isset($group) || !$group instanceof Group) {
            $this->error('Group not found');
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

        $group->users()->detach($user->id);
        $this->info('User ' . (isset($user->email) ? $user->email : $user->id) . ' removed from group ' . $group->name);
    }
}
