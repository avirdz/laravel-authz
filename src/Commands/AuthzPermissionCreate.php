<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Validators\Permission as PermissionValidator;
use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Console\Command;

class AuthzPermissionCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:permission-create {key_name} {value} {description?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new permission';

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
        $input = [
            'key_name' => $this->argument('key_name'),
            'value' => $this->argument('value'),
            'description' => (string) $this->argument('description'),
        ];

        $validator = PermissionValidator::getValidator($input);

        if ($validator->passes()) {
            $permission = Permission::create($input);
            $this->info('Permission created: ' . $permission->id);
        } else {
            $this->error($validator->errors()->first());
        }
    }
}
