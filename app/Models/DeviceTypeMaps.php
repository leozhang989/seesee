<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceTypeMaps extends Model
{
    protected $table = 'device_type_maps';

    protected $fillable = [
        'device_type',
        'starttime'
    ];
}
