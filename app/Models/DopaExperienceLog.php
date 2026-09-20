<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DopaExperienceLog extends Model
{
    protected $fillable = [
        'dopa_id',
        'amount',
        'action',
        'source_type',
        'source_id',
    ];

    public function dopa()
    {
        return $this->belongsTo(Dopa::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}