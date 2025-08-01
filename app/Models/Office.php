<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'branch_name',
        'experience',
        'employee_id',
        'smart_id_image',
        'has_smart_id',
    ];
}
