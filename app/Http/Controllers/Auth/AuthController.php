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
use Illuminate\Support\Facades\DB;

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

                $subscription = null; // Initialize subscription variable

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
                            // Create Stripe Customer
                            $user->createOrGetStripeCustomer();

                            // Create the subscription with the correct price ID
                            $stripeSubscription = $user->newSubscription('default', $plan->stripe_price_id)
                                ->skipTrial()
                                ->noProrate()
                                ->create($request->payment_method_id, []);

                            // After subscription is created, update the payment method as default
                            $user->updateDefaultPaymentMethod($request->payment_method_id);

                            // Instead of trying to update the Cashier-created subscription,
                            // let's delete it and create a new one with our custom fields

                            // First, get the subscription from Cashier
                            $cashierSubscription = $user->subscriptions()->latest()->first();

                            if ($cashierSubscription) {
                                // Store the stripe_id before deleting
                                $stripeId = $cashierSubscription->stripe_id;
                                $stripeStatus = $cashierSubscription->stripe_status;

                                // Delete the Cashier-created subscription record (but keep the Stripe subscription)
                                $cashierSubscription->delete();

                                // Create our own subscription record with all the fields we need
                                $subscription = \App\Models\Subscription::create([
                                    'user_id' => $user->id,
                                    'type' => 'default',
                                    'stripe_id' => $stripeId,
                                    'stripe_status' => $stripeStatus,
                                    'stripe_price' => $plan->stripe_price_id,
                                    'plan_id' => $plan->id,
                                    'quantity' => 1,
                                    'trial_ends_at' => null,
                                    'ends_at' => null,
                                ]);

                                Log::channel('single')->info('Subscription recreated successfully', [
                                    'user_id' => $user->id,
                                    'plan_id' => $plan->id,
                                    'stripe_id' => $stripeId,
                                    'subscription_id' => $subscription->id
                                ]);
                            } else {
                                throw new \Exception('Cashier subscription not found after creation');
                            }
                        } catch (\Exception $e) {
                            Log::channel('single')->error('Stripe subscription failed', [
                                'error' => $e->getMessage(),
                                'user_id' => $user->id,
                                'trace' => $e->getTraceAsString()
                            ]);
                            throw new \Exception('Payment processing failed: ' . $e->getMessage());
                        }
                    } else {
                        // Handle free plan
                        $subscription = $user->subscriptions()->create([
                            'type' => 'default',
                            'plan_id' => $plan->id,
                            'stripe_id' => 'free_' . $user->id . '_' . time(), // Generate a unique identifier for free plans
                            'stripe_status' => 'active',
                            'quantity' => 1
                        ]);

                        Log::channel('single')->info('Free subscription created for manager', [
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

                // If user was created but subscription failed, ensure it's deleted
                if (isset($user)) {
                    $user->delete();
                }

                return response()->json([
                    'error' => 'Registration failed: ' . $e->getMessage()
                ], 500);
            }
        });
    }
}
