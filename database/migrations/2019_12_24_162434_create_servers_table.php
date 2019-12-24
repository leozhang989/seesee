<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('gid', 0)->default(0)->comment('server group id');
            $table->unsignedTinyInteger('type', 0)->default(1)->comment('server type');
            $table->string('name', '255')->nullable(false)->default('')->comment('server name');
            $table->string('address', '255')->nullable(false)->default('')->comment('server address');
            $table->string('icon', '255')->nullable(false)->default('')->comment('server icon');
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
        Schema::dropIfExists('servers');
    }
}
