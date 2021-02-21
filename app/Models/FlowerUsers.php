<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlowerUsers extends Model
{
    protected $table = 'flower_users';

    protected $fillable = [
        'processed'
    ];
}
