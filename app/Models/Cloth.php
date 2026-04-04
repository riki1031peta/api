<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cloth extends Model
{
    protected $fillable = [
        'image_path',
        'where_buy',
        'description',
    ];
}
