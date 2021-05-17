<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceIdentifierMaps extends Model
{
    protected $table = 'device_identifier_maps';

    protected $fillable = [
        'device_identifier',
        'device_type',
        'starttime'
    ];
}
