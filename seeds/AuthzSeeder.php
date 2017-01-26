<?php

use Illuminate\Database\Seeder;

class AuthzSeeder extends Seeder
{
    /**
     * Run the authz seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AuthzGroupSeeder::class);
        $this->call(AuthzPermissionSeeder::class);
    }
}
