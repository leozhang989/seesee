<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerUser extends Model
{
    protected $table = 'flower_users';

    protected $connection = 'mysql_newflower';

    protected $fillable = [
        'code', 'vip_expireat', 'free_expireat', 'group', 'leftdays', 'ifsync', 'continuous_signtimes', 'uuid', 'is_permanent_vip', 'last_login'
    ];
}
