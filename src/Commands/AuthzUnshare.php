<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzUnshare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:unshare {user} {resource} {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unshare a resource with a user';

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
        $resourceId = $this->argument('resource');
        $resourceClass = $this->argument('class');

        $this->call('authz:shareable', [
            'user' => $userId,
            'value' => Permission::DENIED,
            'resource' => $resourceId,
            'class' => $resourceClass,
        ]);
    }
}
