<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seeuser extends Model
{
    protected $table = 'seeusers';

    protected $fillable = [
        'name', 'gid', 'email', 'password', 'phone', 'free_vip_expired', 'vip_expired', 'vip_left_time', 'is_permanent_vip', 'uuid', 'permanent_expired', 'permanent_device'
    ];
}
