<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcement';

    protected $fillable = ['content', 'redirect_url', 'logic', 'online'];

}
