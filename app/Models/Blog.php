<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HasFactory;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'content',
    ];
}
