<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServersList extends Model
{
    protected $table = 'servers_list';

    protected $fillable = ['server_gid', 'name', 'appname', 'address', 'icon', 'type', 'start_port', 'end_port', 'encrypt_type', 'server_pwd'];

}
