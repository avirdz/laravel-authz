<?php

namespace Avirdz\LaravelAuthz\Commands;

use Illuminate\Console\Command;

class AuthzResourcePermissionException extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:resource-add-exception {user} {shared} {permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a permission exception on specific resource';

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
        //
    }
}
