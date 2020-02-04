<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetEmailLog extends Model
{
    protected $table = 'reset_email_logs';

    protected $fillable = [
        'email', 'reset_token', 'reset_url', 'status', 'send_time', 'valid_time'
    ];
}
