<?php

namespace Avirdz\LaravelAuthz\Commands;

use Illuminate\Console\Command;

class AuthzShareResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:share-resource {user} {resource} {type}';

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
        $userId = $this->argument('user');
        $resourceId = $this->argument('resource');
        $resourceClass = $this->argument('type');

        if (!class_exists($resourceClass)) {
            $this->error('Class of type ' . $resourceClass . ' does not exist');
            exit(1);
        }

        if (is_numeric($resourceId) && $resourceId > 0) {
            $resource = $resourceClass::find($resourceId);
        }

        if (!isset($resource) || !$resource instanceof $resourceClass) {
            $this->error('Shareable resource not found');
            exit(1);
        }

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

        // @todo attach the user to the resource
    }
}
