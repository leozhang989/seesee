<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupGiftlLog extends Model
{
    protected $table = 'group_gift_logs';

    protected $fillable = [
        'user_uuid', 'get_time', 'gift_days', 'app_name'
    ];
}
