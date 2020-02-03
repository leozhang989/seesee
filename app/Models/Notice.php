<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'notices';

    protected $fillable = [
        'notice_title', 'notice_summary', 'notice_content', 'start_time', 'end_time', 'online'
    ];
}
