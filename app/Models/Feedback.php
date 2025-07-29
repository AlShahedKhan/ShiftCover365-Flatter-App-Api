<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'overall_rating',
        'feature_used',
        'suggestions',
        'other_user_type',
        'other_feature'
    ];

    protected $casts = [
        'overall_rating' => 'integer',
    ];

    /**
     * Get the user who submitted this feedback
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get feedback by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope to get feedback by rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('overall_rating', $rating);
    }
}
