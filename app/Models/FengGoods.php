<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FengGoods extends Model
{
    protected $table = 'goods';

    protected $connection = 'mysql_newfeng';

    protected $fillable = ['name', 'describe', 'commodity_code', 'service_date', 'price', 'status', 'type'];

}
