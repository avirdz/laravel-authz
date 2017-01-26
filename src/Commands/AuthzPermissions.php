<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:permissions {page?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List of permissions';

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

        $permissions = Permission::paginate(100, ['*'], 'page', $page);

        if ($permissions->isEmpty()) {
            $this->info('No permissions');
        } else {
            $output = $permissions->toArray();
            $this->table([
                'Id',
                'Key name',
                'Description',
                'Value',
            ], $output['data']);
        }
    }
}
