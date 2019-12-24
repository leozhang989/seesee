<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', '20')->nullable(false)->default('')->comment('user uuid');
            $table->string('device_code', '100')->nullable(false)->default('')->comment('user device_code');
            $table->unsignedTinyInteger('is_master', 0)->nullable(false)->default(1)->comment('user master device');
            $table->unsignedTinyInteger('status', 0)->nullable(false)->default(1)->comment('user device status');
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
        Schema::dropIfExists('devices');
    }
}
