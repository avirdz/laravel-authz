<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionShareableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_shareable', function (Blueprint $table) {
            $table->bigInteger('shareable_id')->unsigned();
            $table->bigInteger('permission_id')->unsigned();

            $table->primary(['shareable_id', 'permission_id']);

            $table->foreign('shareable_id')->references('shared_id')
                ->on('shareables')->onDelete('cascade');

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
        Schema::drop('permission_shareable');
    }
}
