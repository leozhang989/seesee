<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerVipSetLogs extends Model
{
    protected $table = 'flower_vip_set_logs';

    protected $fillable = [
        'admin_name', 'flower_uuid', 'see_email', 'viptime'
    ];
}
