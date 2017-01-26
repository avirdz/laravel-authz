<?php

namespace Avirdz\LaravelAuthz\Commands;

use Illuminate\Console\Command;

class AuthzUnshareResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:unshare-resource {user} {resource}';

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
        //
    }
}
