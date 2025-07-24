<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::channel('single')->info('Registration request received', [
                'request' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|in:professional,manager',
                'office_id' => 'required|exists:offices,id',
                'subscription' => 'required_if:role,manager',
                'subscription.plan_id' => 'required_if:role,manager|exists:plans,id',
                'payment_method_id' => 'required_if:role,manager'
            ]);

            if ($validator->fails()) {
                Log::channel('single')->warning('Validation failed during registration', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'office_id' => $request->office_id
            ]);

            Log::channel('single')->info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            if ($request->role === 'manager') {
                $plan = Plan::findOrFail($request->subscription['plan_id']);

                if ($plan->price > 0) {
                    if (empty($plan->stripe_price_id)) {
                        throw new \Exception('Stripe price ID not configured for this plan.');
                    }

                    if (empty($request->payment_method_id)) {
                        throw new \Exception('Payment method is required for paid plans.');
                    }

                    Log::channel('single')->info('Creating Stripe subscription', [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'stripe_price_id' => $plan->stripe_price_id
                    ]);

                    try {
                        $user->createOrGetStripeCustomer();

                        $stripeSubscription = $user->newSubscription('default', $plan->stripe_price_id)
                            ->create($request->payment_method_id);

                        $user->updateDefaultPaymentMethod($request->payment_method_id);

                        $user->subscriptions()->where('stripe_id', $stripeSubscription->id)->update([
                            'plan_id' => $plan->id
                        ]);

                        $subscription = $user->subscriptions()->where('stripe_id', $stripeSubscription->id)->first();

                        Log::channel('single')->info('Stripe subscription created', [
                            'user_id' => $user->id,
                            'plan_id' => $plan->id,
                            'subscription_id' => $subscription->id
                        ]);
                    } catch (\Exception $e) {
                        Log::channel('single')->error('Stripe subscription failed', [
                            'error' => $e->getMessage(),
                            'user_id' => $user->id,
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw new \Exception('Payment processing failed: ' . $e->getMessage());
                    }
                } else {
                    // FREE plan logic
                    $subscription = $user->subscriptions()->create([
                        'type' => 'default',
                        'plan_id' => $plan->id,
                        'stripe_status' => 'active',
                        'quantity' => 1,
                        'stripe_id' => 'free_' . \Str::uuid(), // Ensure unique value
                    ]);

                    Log::channel('single')->info('Free plan subscription created', [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'subscription_id' => $subscription->id
                    ]);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::channel('single')->info('Registration completed successfully', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);

            return response()->json([
                'message' => 'Registration successful',
                'user' => $user->load('subscription.plan'),
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            Log::channel('single')->error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($user)) {
                $user->delete();
            }

            return response()->json([
                'error' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

}
