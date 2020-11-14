<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerAdServers extends Model
{
    protected $table = 'flower_ad_servers';

    protected $fillable = ['type', 'name', 'address', 'icon', 'cid', 'start_port', 'end_port', 'encrypt_type', 'server_pwd'];

}
