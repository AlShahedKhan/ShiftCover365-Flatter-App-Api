<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'office_id',
        'shift_type_id',
        'start_time',
        'end_time',
        'location',
        'department',
        'budget'
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function shiftType()
    {
        return $this->belongsTo(ShiftTypes::class);
    }

    public function applications()
    {
        return $this->hasMany(ShiftApplication::class);
    }
}
