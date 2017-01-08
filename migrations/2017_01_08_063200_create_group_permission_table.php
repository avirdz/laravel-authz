<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_permission', function (Blueprint $table) {
            $table->bigInteger('group_id')->unsigned()->index();
            $table->bigInteger('permission_id')->unsigned()->index();
            $table->tinyInteger('permission_status')->unsigned();

            $table->primary(['group_id', 'permission_id']);

            $table->foreign('group_id')->references('id')
                ->on('groups')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')
                ->on('permissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('group_permission');
    }
}
