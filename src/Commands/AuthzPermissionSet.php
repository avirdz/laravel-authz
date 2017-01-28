<?php

namespace Avirdz\LaravelAuthz\Commands;

use Avirdz\LaravelAuthz\Models\Group;
use Avirdz\LaravelAuthz\Models\Permission;
use Avirdz\LaravelAuthz\Models\Shareable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class AuthzPermissionSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authz:permission-set {permission} {value} {id?} {resource?} {--class=} {--type=group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set permission configuration';

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
        $permissionId = $this->argument('permission');
        $value = $this->argument('value');
        $id = $this->argument('id');
        $type = $this->option('type');

        // get permmission
        if (is_numeric($permissionId) && $permissionId > 0) {
            $permission = Permission::find($permissionId);
        } elseif (is_string($permissionId) && strlen($permissionId)) {
            $permission = Permission::where('key_name', $permissionId)->first();
        }

        if (!isset($permission) || !$permission instanceof Permission) {
            $this->error('Permission not found');
            exit(1);
        }

        // get item
        if ($id !== null) {
            $userClass = config('authz.user_model');
            $textValues = [
                0 => 'DENIED',
                1 => 'GRANTED',
            ];

            if ($type === 'user') {
                if (is_numeric($id) && $id > 0) {
                    $item = $userClass::find($id);
                } elseif (is_string($id)) {
                    $item = $userClass::where('email', $id)->first();
                }
            } elseif ($type === 'group') {
                if (is_numeric($id) && $id > 0) {
                    $item = Group::find($id);
                } elseif (is_string($id) && strlen($id)) {
                    $item = Group::where('name', $id)->first();
                }
            }

            if (!isset($item)) {
                $this->error(ucfirst($type) . ' not found');
                exit(1);
            }

            if (is_numeric($value) && ($value == Permission::GRANTED || $value == Permission::DENIED)) {
                $value = (int) $value;
            } else {
                $this->error('Invalid value');
                exit(1);
            }

            if ($item instanceof Group) {
                $permission->groups()
                    ->syncWithoutDetaching([$item->id => ['permission_status' => $value]]);
                $this->info($permission->key_name . ' ' . $textValues[$value] . ' to ' . $item->name);
            } elseif ($item instanceof $userClass) {
                if ($this->hasArgument('resource')) {
                    $class = $this->option('class');
                    if ($class === null || !class_exists($class)) {
                        $this->error('Invalid class provided');
                        exit(1);
                    }

                    // get resource
                    $resourceId = $this->argument('resource');
                    if (!is_numeric($resourceId) || $resourceId <= 0) {
                        $this->error('Invalid resource value');
                        exit(1);
                    }

                    $resource = $class::find($resourceId);
                    if ($resource === null) {
                        $this->error('Resource not found ' . $class);
                        exit(1);
                    }

//                    if (!method_exists('sharedWith', $resource)) {
//                        $this->error('sharedWith method doesn\'t exist on class ' . get_class($resource)
//                            .'. Adding the AuthzResource trait to the resource may resolve the issue.');
//                        exit(1);
//                    }

                    // get shareable
                    $shareableUser = $resource->sharedWith()
                        ->where('user_id', $item->id)
                        ->first();

                    if ($shareableUser === null) {
                        $this->error('The resource is not shared');
                        exit(1);
                    }

                    // get shareable pivot
                    $shareable = Shareable::find($shareableUser->pivot->id);

                    if ($shareable === null) {
                        $this->error('Shareable record not found');
                        exit(1);
                    }

                    if ($value == Permission::GRANTED) {
                        $shareable->permissionExceptions()->detach([$permission->id]);
                        $this->info($permission->key_name . ' ' . $textValues[$value] . ' to '
                            . (isset($item->email) ? $item->email : $item->id) . ' on '
                            . get_class($resource) . ' ' . $resource->id);
                    } elseif ($value == Permission::DENIED) {
                        $shareable->permissionExceptions()->syncWithoutDetaching([$permission->id]);
                        $this->info($permission->key_name . ' ' . $textValues[$value] . ' to '
                            . (isset($item->email) ? $item->email : $item->id) . ' on '
                            . get_class($resource) . ' ' . $resource->id);
                    }
                } else {
                    $permission->users()
                        ->syncWithoutDetaching([$item->id => ['permission_status' => $value]]);
                    $this->info($permission->key_name . ' ' . $textValues[$value] . ' to '
                        . (isset($item->email) ? $item->email : $item->id));
                }
            }
        } else {
            $values = [
                'custom' => Permission::CUSTOM,
                'root' => Permission::ROOT,
                'any' => Permission::ANY,
                'owner' => Permission::OWNER,
                'shared' => Permission::SHARED,
                'anonymous' => Permission::ANONYMOUS,
                'auth' => Permission::AUTHENTICATED,
            ];


            if (is_string($value) && array_key_exists($value, $values)) {
                $value = $values[$value];
            } elseif (is_numeric($value) && in_array($value, $values)) {
                $value = (int) $value;
            } else {
                $this->error('Invalid value');
                exit(1);
            }

            $permission->value = $value;
            $permission->save();

            $this->info($permission->key_name . ' set to ' . strtoupper($values[array_search($value, $values)]));

            // remove group and users permissions
            if ($permission->value !== Permission::CUSTOM
                && ($permission->groups()->count() || $permission->users()->count())) {
                if ($this->confirm('Do you want to clear groups and users from pivot tables on '
                    . $permission->key_name . '? (y|N)')) {
                    $results = $permission->groups()->detach();
                    $this->info($results . ' groups have been removed from the pivot table');

                    $results = $permission->users()->detach();
                    $this->info($results . ' users have been removed from the pivot table');
                }
            }
        }
    }
}
