<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'transactionId', 'uuid', 'goods_id', 'status', 'goods_name', 'price', 'device_code', 'commodity_code', 'service_date', 'goods_num', 'order_no'
    ];
}
