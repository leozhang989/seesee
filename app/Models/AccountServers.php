<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountServers extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'account_servers';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['cid', 'name', 'address', 'port', 'password', 'secret'];
}
