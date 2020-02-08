<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RechargeLogs extends Model
{
    protected $table = 'recharge_logs';

    protected $fillable = [
        'creater', 'uuid', 'product', 'price', 'is_dealed', 'res_status', 'app_name', 'settlement_id'
    ];
}
