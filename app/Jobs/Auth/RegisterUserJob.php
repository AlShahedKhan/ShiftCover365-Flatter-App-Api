<?php

namespace App\Jobs\Auth;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\Welcome\WelcomeUserMail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RegisterUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected readonly array $userData) {}

    public function handle(): User
    {
        return DB::transaction(function () {
            $user = User::create([
                'first_name' => $this->userData['first_name'],
                'last_name' => $this->userData['last_name'],
                'email' => $this->userData['email'],
                'password' => Hash::make($this->userData['password']),
                'role' => $this->userData['role'] ?? 'user'
            ]);

            // Create subscription if plan_id is provided
            if (!empty($this->userData['plan_id'])) {
                $user->subscription()->create([
                    'plan_id' => $this->userData['plan_id'],
                    'type' => $this->userData['type'] ?? 'default',
                    'stripe_id' => $this->userData['stripe_id'] ?? uniqid('sub_'),
                    'stripe_status' => $this->userData['stripe_status'] ?? 'active',
                    'stripe_price' => $this->userData['stripe_price'] ?? null,
                    'quantity' => $this->userData['quantity'] ?? 1,
                ]);
            }
            Mail::to($user->email)->queue(new WelcomeUserMail(
                firstName: $user->first_name,
                lastName: $user->last_name,
                email: $user->email
            ));
            return $user;
        }, attempts: 3);
    }
}
