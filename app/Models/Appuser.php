<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appuser extends Model
{
    protected $table = 'appusers';

    protected $fillable = [
        'name', 'gid', 'email', 'password', 'phone', 'uuid', 'free_vip_expired', 'vip_expired', 'vip_left_time'
    ];
}
