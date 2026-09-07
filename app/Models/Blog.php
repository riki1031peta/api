<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HasFactory;
use App\Models\Admin;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author',
        'thumbnail',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
