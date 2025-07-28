<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; // JWT এর জন্য
use Laravel\Cashier\Billable; // যদি Stripe ব্যবহার করেন

class User extends Authenticatable implements JWTSubject // JWTSubject implement করুন
{
    use HasFactory, Notifiable, Billable; // HasApiTokens remove করুন

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'present_address',
        'profile_image',
        'office_id',
        'agreement_signed',
        'signature',
        'profile_verified',
        'staff_code_verified',
        'staff_code_hash',
        'id_document',
        'company',
        'branch',
        'experience',
        'location',
        'employee_id',
        'has_smart_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's subscription.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get the user's office.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the user's consent.
     */
    public function consent()
    {
        return $this->hasOne(Consent::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'office_id' => $this->office_id,
        ];
    }
}
