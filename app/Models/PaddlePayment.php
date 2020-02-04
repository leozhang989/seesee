<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaddlePayment extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'paddle_payments';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['currency', 'checkout_id', 'email', 'customer_name', 'payment_method', 'earnings', 'fee', 'sale_gross', 'quantity', 'product_id', 'product_name', 'order_id', 'order_no'];
}
