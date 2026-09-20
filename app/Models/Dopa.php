<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dopa extends Model
{
    protected $fillable = [
        'user_id',
        'experience',
        'level',
        'stage',
        'type',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function experienceLogs()
    {
        return $this->hasMany(DopaExperienceLog::class);
    }
}