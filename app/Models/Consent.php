<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_given',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
    ];

    /**
     * Get the user that owns the consent.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
