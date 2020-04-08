<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipServer extends Model
{
    protected $table = 'vip_servers';

    protected $fillable = ['cid', 'name', 'address', 'icon', 'type', 'start_port', 'end_port', 'encrypt_type', 'server_pwd'];

}
