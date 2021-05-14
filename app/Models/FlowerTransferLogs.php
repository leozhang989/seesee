<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerTransferLogs extends Model
{
    protected $table = 'flower_transfer_logs';

    protected $fillable = [
        'kang_device_code',
        'flower_uuid',
        'device_code',
        'email',
        'transfer_time',
        'vip_type',
        'is_kang',
        'kang_pay_time',
        'kang_device_code'
    ];
}
