<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerSettlementList extends Model
{
    protected $table = 'settlement_list';

    protected $connection = 'mysql_newflower';

    protected $fillable = [
        'settlement_user', 'settlement_status', 'settlement_amount', 'total_money', 'earn_money', 'pay_money'
    ];
}
