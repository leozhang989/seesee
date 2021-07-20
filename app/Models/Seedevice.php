<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seedevice extends Model
{
    protected $table = 'seedevices';

    protected $fillable = ['uuid', 'device_code', 'is_master', 'status', 'free_vip_expired', 'uid', 'transfered', 'transfered_time', 'device_model', 'device_identifier', 'is_permanent_device'];

}
