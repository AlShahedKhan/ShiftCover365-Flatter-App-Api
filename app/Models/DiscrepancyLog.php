<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscrepancyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'status', // 'discrepancy' or 'no_discrepancy'
        'note',
        'type', // 'auto' or 'manual'
    ];
}
