<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementList extends Model
{
    protected $table = 'settlement_list';

    protected $fillable = [
        'settlement_user', 'settlement_status', 'settlement_amount', 'total_money', 'earn_money', 'pay_money'
    ];
}
