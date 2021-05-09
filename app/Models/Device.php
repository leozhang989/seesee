<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    protected $fillable = ['uuid', 'device_code', 'is_master', 'status', 'free_vip_expired', 'uid', 'transfered', 'device_model'];

}
