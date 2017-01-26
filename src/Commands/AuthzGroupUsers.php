<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Group;
use Illuminate\Console\Command;

class AuthzGroupUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:group-users {group} {page?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the users from a group';

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

        if (is_numeric($groupId) && $groupId > 0) {
            $group = Group::find($groupId);
        } elseif (is_string($groupId) && strlen($groupId)) {
            $group = Group::where('name', $groupId)->limit(1)->first();
        }

        if (!isset($group) || !$group instanceof Group) {
            $this->error('Group not found');
            exit(1);
        }

        $page = $this->argument('page');
        if ($page === null || !is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        $users = $group->users()->paginate(100, ['id', 'name', 'email'], 'page', $page);

        if ($users->isEmpty()) {
            $this->info('No users');

            if ($group->id === Group::ANONYMOUS_ID || $group->id === Group::UNASSIGNED_ID) {
                $this->info($group->name . ' must not have any user');
            }
        } else {
            $output = $users->makeHidden('pivot')->toArray();
            $this->table([
                'Id',
                'Name',
                'Email',
            ], $output);
            // @todo add pagination info
        }
    }
}
