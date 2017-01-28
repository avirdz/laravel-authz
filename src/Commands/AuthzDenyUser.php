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
        $permission = $this->argument('permission');
        $id = $this->argument('user');

        $this->call('authz:permission-set', [
            'permission' => $permission,
            'id' => $id,
            'value' => Permission::DENIED,
            '--type' => 'user',
        ]);
    }
}
