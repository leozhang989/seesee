<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $table = 'app_versions';

    protected $fillable = ['app_version', 'content', 'expired_date', 'testflight_url', 'online'];
}
