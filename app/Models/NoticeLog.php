<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeLog extends Model
{
    protected $table = 'notice_logs';

    protected $fillable = [
        'uuid', 'notice_id'
    ];
}
