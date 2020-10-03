<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FengDevice extends Model
{
    protected $table = 'devices';

    protected $connection = 'mysql_newfeng';

    protected $fillable = [
        'uid', 'device_code', 'is_master', 'status'
    ];
}
