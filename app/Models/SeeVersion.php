<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeeVersion extends Model
{
    protected $table = 'see_versions';

    protected $fillable = ['app_version', 'content'];
}
