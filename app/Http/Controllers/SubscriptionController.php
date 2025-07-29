<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuthHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Get current subscription details
     */
    public function getCurrentSubscription()
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can access subscription details', 403);
        }

        $subscription = $user->subscription('default');
        // Fix: If $subscription is a relation, get the first record
        if ($subscription instanceof \Illuminate\Database\Eloquent\Relations\HasOne || $subscription instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            $subscription = $subscription->first();
        }

        if (!$subscription) {
            return ResponseHelper::error('No active subscription found', 404);
        }

        $planItem = (is_object($subscription->items) && method_exists($subscription->items, 'first')) ? $subscription->items->first() : null;
        $stripe_price = $planItem->stripe_price ?? $subscription->stripe_price_id ?? $subscription->stripe_price ?? null;
        $plan = $stripe_price ? Plan::where('stripe_price_id', $stripe_price)->first() : null;

        return ResponseHelper::success([
            'subscription' => [
                'id' => $subscription->id ?? null,
                'status' => $subscription->stripe_status ?? null,
                'current_period_end' => method_exists($subscription, 'asStripeSubscription') ? $subscription->asStripeSubscription()->current_period_end : null,
                'plan' => [
                    'id' => $stripe_price,
                    'name' => $plan?->name ?? 'Unknown Plan',
                    'price' => $plan?->price ?? 0,
                ]
            ]
        ], 'Subscription details retrieved successfully', 200);
    }

    /**
     * Update subscription plan
     */
    public function updateSubscription(Request $request)
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can update subscription', 403);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        try {
            $plan = Plan::findOrFail($validated['plan_id']);

            if (empty($plan->stripe_price_id)) {
                return ResponseHelper::error('Selected plan is not configured for payments', 400);
            }

            $subscription = $user->subscription('default');
            if ($subscription instanceof \Illuminate\Database\Eloquent\Relations\HasOne || $subscription instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                $subscription = $subscription->first();
            }

            if (!$subscription) {
                return ResponseHelper::error('No active subscription found', 404);
            }

            // Update the subscription to the new plan
            $subscription->swap($plan->stripe_price_id);

            Log::info('Subscription updated', [
                'user_id' => $user->id,
                'old_plan' => $subscription->items->first()->stripe_price,
                'new_plan' => $plan->stripe_price_id
            ]);

            return ResponseHelper::success([
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->stripe_status,
                    'plan' => [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                    ]
                ]
            ], 'Subscription updated successfully', 200);

        } catch (\Exception $e) {
            Log::error('Subscription update error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'plan_id' => $validated['plan_id'],
                'error' => $e->getMessage()
            ]);

            return ResponseHelper::error('Failed to update subscription. Please try again.', 500);
        }
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(Request $request)
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can update payment method', 403);
        }

        $validated = $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        try {
            // Update the default payment method
            $user->updateDefaultPaymentMethod($validated['payment_method_id']);

            Log::info('Payment method updated', [
                'user_id' => $user->id,
                'payment_method_id' => $validated['payment_method_id']
            ]);

            return ResponseHelper::success([
                'message' => 'Payment method updated successfully',
                'payment_method' => [
                    'id' => $validated['payment_method_id'],
                    'type' => $user->pm_type,
                    'last_four' => $user->pm_last_four,
                ]
            ], 'Payment method updated successfully', 200);

        } catch (\Exception $e) {
            Log::error('Payment method update error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'payment_method_id' => $validated['payment_method_id'],
                'error' => $e->getMessage()
            ]);

            return ResponseHelper::error('Failed to update payment method. Please try again.', 500);
        }
    }

    /**
     * Get available plans
     */
    public function getAvailablePlans()
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can view plans', 403);
        }

        $plans = Plan::where('stripe_price_id', '!=', null)
                    ->where('stripe_price_id', '!=', '')
                    ->get();

        return ResponseHelper::success([
            'plans' => $plans
        ], 'Available plans retrieved successfully', 200);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription()
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can cancel subscription', 403);
        }

        try {
            $subscription = $user->subscription('default');

            if (!$subscription) {
                return ResponseHelper::error('No active subscription found', 404);
            }

            // Cancel the subscription at the end of the current period
            $subscription->cancel();

            Log::info('Subscription cancelled', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id
            ]);

            return ResponseHelper::success([
                'message' => 'Subscription cancelled successfully',
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->stripe_status,
                    'ends_at' => $subscription->ends_at,
                ]
            ], 'Subscription cancelled successfully', 200);

        } catch (\Exception $e) {
            Log::error('Subscription cancellation error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return ResponseHelper::error('Failed to cancel subscription. Please try again.', 500);
        }
    }

    /**
     * Resume cancelled subscription
     */
    public function resumeSubscription()
    {
        AuthHelper::checkUser();
        $user = Auth::user();

        if ($user->role !== 'manager') {
            return ResponseHelper::error('Only managers can resume subscription', 403);
        }

        try {
            $subscription = $user->subscription('default');

            if (!$subscription) {
                return ResponseHelper::error('No subscription found', 404);
            }

            if (!$subscription->canceled()) {
                return ResponseHelper::error('Subscription is not cancelled', 400);
            }

            // Resume the subscription
            $subscription->resume();

            Log::info('Subscription resumed', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id
            ]);

            return ResponseHelper::success([
                'message' => 'Subscription resumed successfully',
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->stripe_status,
                ]
            ], 'Subscription resumed successfully', 200);

        } catch (\Exception $e) {
            Log::error('Subscription resume error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return ResponseHelper::error('Failed to resume subscription. Please try again.', 500);
        }
    }
}
