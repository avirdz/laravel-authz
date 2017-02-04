<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Validators\GroupValidator;
use Avirdz\LaravelAuthz\Models\Group;
use Illuminate\Console\Command;

class AuthzGroupCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:group-create {name} {description?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new group';

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
            'name' => $this->argument('name'),
            'description' => (string) $this->argument('description'),
        ];

        $validator = GroupValidator::getValidator($input);

        if ($validator->passes()) {
            $group = Group::create($input);
            $this->info('Group created: ' . $group->id);
        } else {
            $this->error($validator->errors()->first());
        }
    }
}
