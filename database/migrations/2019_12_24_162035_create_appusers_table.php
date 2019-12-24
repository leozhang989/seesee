<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appusers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', '60')->nullable(false)->default('')->comment('user name');
            $table->unsignedTinyInteger('gid', 0)->default(0)->comment('user group id');
            $table->string('email', '255')->nullable(false)->default('')->comment('user email');
            $table->string('password', '255')->nullable(false)->default('')->comment('user password');
            $table->string('phone', '20')->nullable(false)->default('')->comment('user phone');
//            $table->string('uuid', '20')->nullable(false)->default('')->comment('user uuid');
            $table->unsignedInteger('free_vip_expired', 0)->nullable(false)->default(0)->comment('user free_vip_expired');
            $table->unsignedInteger('vip_expired', 0)->nullable(false)->default(0)->comment('user vip_expired');
            $table->unsignedInteger('vip_left_time', 0)->nullable(false)->default(0)->comment('user vip_left_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appusers');
    }
}
