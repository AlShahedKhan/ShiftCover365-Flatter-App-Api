<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'user_id'
    ];

    /**
     * Get the user who created this FAQ
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
