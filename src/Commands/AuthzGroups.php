<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Group;
use Illuminate\Console\Command;

class AuthzGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:groups {page?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List of groups';

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
        $page = $this->argument('page');
        if ($page === null || !is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        $groups = Group::paginate(100, ['*'], 'page', $page);

        if ($groups->isEmpty()) {
            $this->info('No groups');
        } else {
            $output = $groups->toArray();
            $this->table([
                'Id',
                'Name',
                'Description',
                'Default group?',
                'System group?',
            ], $output['data']);
        }
    }
}
