<?php

namespace App\Http\Controllers\Auth;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        return DB::transaction(function () use ($request) {
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

                $subscription = null;

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

                        $user->createOrGetStripeCustomer();

                        $stripeSubscription = $user->newSubscription('default', $plan->stripe_price_id)
                            ->skipTrial()
                            ->noProrate()
                            ->create($request->payment_method_id);

                        $user->updateDefaultPaymentMethod($request->payment_method_id);

                        // Debug: Log what we're looking for
                        Log::channel('single')->info('Looking for subscription', [
                            'stripe_id' => $stripeSubscription->id,
                            'user_id' => $user->id
                        ]);

                        // Wait a moment for database consistency
                        sleep(1);

                        // Try multiple ways to find the subscription
                        $subscription = $user->subscriptions()
                            ->where('stripe_id', $stripeSubscription->id)
                            ->first();

                        if (!$subscription) {
                            // Try finding by type and user
                            $subscription = $user->subscriptions()
                                ->where('type', 'default')
                                ->latest()
                                ->first();

                            Log::channel('single')->info('Found subscription by type', [
                                'subscription_id' => $subscription ? $subscription->id : 'not found',
                                'stripe_id' => $subscription ? $subscription->stripe_id : 'N/A'
                            ]);
                        }

                        if (!$subscription) {
                            // Log all subscriptions for this user to debug
                            $allSubs = $user->subscriptions()->get();
                            Log::channel('single')->error('Could not find subscription', [
                                'all_subscriptions' => $allSubs->toArray(),
                                'looking_for_stripe_id' => $stripeSubscription->id
                            ]);
                            throw new \Exception('Failed to retrieve created subscription');
                        }

                        // Update the subscription with our custom fields
                        $subscription->plan_id = $plan->id;
                        $subscription->stripe_price_id = $plan->stripe_price_id;
                        $subscription->save();

                        Log::channel('single')->info('Subscription updated with custom fields', [
                            'subscription_id' => $subscription->id,
                            'plan_id' => $plan->id,
                            'stripe_price_id' => $plan->stripe_price_id
                        ]);

                        Log::channel('single')->info('Stripe subscription created', [
                            'user_id' => $user->id,
                            'plan_id' => $plan->id,
                            'subscription_id' => $subscription->id
                        ]);
                    } else {
                        // FREE plan logic
                        $subscription = $user->subscriptions()->create([
                            'type' => 'default',
                            'plan_id' => $plan->id,
                            'stripe_status' => 'active',
                            'quantity' => 1,
                            'stripe_id' => 'free_' . Str::uuid(), // Ensure unique value
                        ]);

                        Log::channel('single')->info('Free plan subscription created', [
                            'user_id' => $user->id,
                            'plan_id' => $plan->id,
                            'subscription_id' => $subscription->id
                        ]);
                    }
                } else {
                    // For professional users, no subscription handling is required during registration
                    Log::channel('single')->info('Professional user registered without subscription', [
                        'user_id' => $user->id
                    ]);
                }

                // JWT Token creation
                $token = JWTAuth::fromUser($user);

                Log::channel('single')->info('Registration completed successfully', [
                    'user_id' => $user->id,
                    'role' => $user->role
                ]);

                return response()->json([
                    'success' => true,
                    'status_code' => 201,
                    'message' => 'Registration successful',
                    'user' => $user->loadMissing(['office', 'subscriptions' => function($query) {
                        $query->with('plan')->latest()->first();
                    }]),
                    'access_token' => $token,
                    'token_type' => 'Bearer'
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
                    'success' => false,
                    'status_code' => 500,
                    'error' => 'Registration failed: ' . $e->getMessage()
                ], 500);
            }
        });
    }
}
