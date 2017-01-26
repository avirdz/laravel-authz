<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzPermissionDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:permission-delete {permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a permission';

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
        $id = $this->argument('permission');

        if (is_numeric($id) && $id > 0) {
            $permission = Permission::find($id);
        } elseif (is_string($id) && strlen($id)) {
            $permission = Permission::where('key_name', $id)->limit(1)->first();
        }

        if (isset($permission) && $permission instanceof Permission) {
            $permission->delete();
            $this->info('Permission deleted');
        } else {
            $this->error('Permission not found');
        }
    }
}
