<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldDevice extends Model
{
    protected $table = 'devices_copy_20210725';

    protected $fillable = ['uuid', 'device_code', 'is_master', 'status', 'free_vip_expired', 'uid', 'transfered', 'transfered_time', 'device_model', 'device_identifier'];

}
