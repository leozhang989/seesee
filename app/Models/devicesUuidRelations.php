<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class devicesUuidRelations extends Model
{
    protected $table = 'devices_uuid_relations';

    protected $fillable = ['uuid', 'device_code', 'free_vip_expired', 'uid'];

}
