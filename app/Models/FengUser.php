<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FengUser extends Model
{
    protected $table = 'users';

    protected $connection = 'mysql_newfeng';

    protected $fillable = [
        'name', 'email', 'password', 'uuid', 'free_expireat', 'vip_expireat', 'vip_left_expireat', 'ad_vip_expireat', 'integral', 'feng_group', 'ad_group'
    ];
}
