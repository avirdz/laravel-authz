<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Group;
use Illuminate\Console\Command;

class AuthzGroupDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:group-delete {group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a group';

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
        $id = $this->argument('group');

        if (is_numeric($id) && $id > 0) {
            $group = Group::find($id);
        } elseif (is_string($id) && strlen($id)) {
            $group = Group::where('name', $id)->limit(1)->first();
        }

        if (isset($group) && $group instanceof Group) {
            $group->delete();
            $this->info('Group deleted');
        } else {
            $this->error('Group not found');
        }
    }
}
