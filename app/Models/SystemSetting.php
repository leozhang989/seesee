<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = ['name', 'value'];

    public static function checkKey($accessKey){
        return DB::table('system_settings')->where('name', $accessKey)->first();
    }

    public static function getValueByName($name){
        return DB::table('system_settings')->where('name', $name)->value('value');
    }
}
