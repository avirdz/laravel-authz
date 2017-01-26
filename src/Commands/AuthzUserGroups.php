<?php

namespace Avirdz\LaravelAuthz\Commands;

use Illuminate\Console\Command;

class AuthzUserGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:user-groups {user} {page?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List a user\'s groups';

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
        $userId = $this->argument('user');

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

        $page = $this->argument('page');
        if ($page === null || !is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        $groups = $user->groups()->paginate(100, ['*'], 'page', $page);

        if ($groups->isEmpty()) {
            $this->info('No groups');
        } else {
            $output = $groups->makeHidden('pivot')->toArray();
            $this->table([
                'Id',
                'Name',
                'Description',
            ], $output);
            // @todo add pagination info
        }
    }
}
