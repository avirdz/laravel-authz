<?php

use Illuminate\Database\Seeder;
use Avirdz\LaravelAuthz\Models\Group;

class AuthzGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => Group::SYS_ADMIN_ID,
                'name' => 'System Administrator',
                'description' => 'This group manages the whole system, users in this group are root users',
            ],
            [
                'id' => Group::UNASSIGNED_ID,
                'name' => 'Unassigned',
                'description' => 'Every user without assignation of groups, has this group by default',
            ],
            [
                'id' => Group::ANONYMOUS_ID,
                'name' => 'Anonymous',
                'description' => 'Unauthenticated users',
            ],
        ]);
    }
}
