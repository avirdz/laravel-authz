<?php

namespace Avirdz\LaravelAuthz\Commands;

use Artisan;
use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzShareable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:shareable {user} {resource} {value} {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Share a resource with a user';

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
        $id = $this->argument('user');
        $resourceId = $this->argument('resource');
        $value = $this->argument('value');
        $class = $this->argument('class');

        $userClass = config('authz.user_model');

        if (is_numeric($value) && ($value == Permission::GRANTED || $value == Permission::DENIED)) {
            $value = (int) $value;
        } else {
            $this->error('Invalid value');
            exit(1);
        }

        if (is_numeric($id) && $id > 0) {
            $item = $userClass::find($id);
        } elseif (is_string($id)) {
            $item = $userClass::where('email', $id)->first();
        }

        if (!isset($item)) {
            $this->error('User not found');
            exit(1);
        }

        if ($class === null || !class_exists($class)) {
            $this->error('Invalid class provided');
            exit(1);
        }

        if ($value == Permission::GRANTED) {
            $item->sharedWith()->attach([$resourceId]);
            $this->info($class . ' shared to '
                . (isset($item->email) ? $item->email : $item->id));
        } elseif ($value == Permission::DENIED) {
            $item->sharedWith()->detach([$resourceId]);
            $this->info($class . ' unshared to '
                . (isset($item->email) ? $item->email : $item->id));
        }
    }
}
