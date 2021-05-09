<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferLogs extends Model
{
    protected $table = 'transfer_logs';

    protected $fillable = [
        'old_uuid', 'device_code', 'email', 'transfer_time', 'vip_type'
    ];
}
