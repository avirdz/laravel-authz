<?php

use Avirdz\LaravelAuthz\Models\Permission;
use Illuminate\Database\Seeder;

class AuthzPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            [
                'key_name' => 'groups.view',
                'description' => 'View groups',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'groups.create',
                'description' => 'Create a group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'groups.update',
                'description' => 'Modify a group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'groups.delete',
                'description' => 'Delete a group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'groups.add-user',
                'description' => 'Add users to a group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'groups.remove-user',
                'description' => 'Remove users from a group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'permissions.view',
                'description' => 'View permissions',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'permissions.create',
                'description' => 'Create a permission',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'permissions.update',
                'description' => 'Modify a group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'permissions.delete',
                'description' => 'Delete a permission',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'permissions.grant',
                'description' => 'Set permission granted to a user or group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'permissions.deny',
                'description' => 'Set permission denied to a user or group',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'shareables.share',
                'description' => 'Share a resource with a user',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'shareables.unshare',
                'description' => 'Unshare a resource with a user',
                'value' => Permission::ROOT,
            ],
            [
                'key_name' => 'shareables.deny',
                'description' => 'Set permission denied to a user on a shared resource',
                'value' => Permission::ROOT,
            ],
        ]);
    }
}
