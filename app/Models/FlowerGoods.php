<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerGoods extends Model
{
    protected $table = 'flower_goods';

    protected $connection = 'mysql_newflower';

    protected $fillable = ['name', 'describe', 'commodity_code', 'service_date', 'price', 'status', 'type', 'created_at', 'updated_at'];

}
