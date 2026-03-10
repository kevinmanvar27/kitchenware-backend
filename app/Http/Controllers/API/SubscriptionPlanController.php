<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Subscription Plans",
 *     description="API endpoints for subscription plans"
 * )
 */
class SubscriptionPlanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/subscription-plans",
     *     summary="Get all active subscription plans",
     *     tags={"Subscription Plans"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="plans", type="array", @OA\Items(ref="#/components/schemas/SubscriptionPlan"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription-plans/{id}",
     *     summary="Get a specific subscription plan",
     *     tags={"Subscription Plans"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="plan", ref="#/components/schemas/SubscriptionPlan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Plan not found"
     *     )
     * )
     */
    public function show($id)
    {
        $plan = SubscriptionPlan::active()->find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'plan' => $plan
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription-plans/featured",
     *     summary="Get featured subscription plans",
     *     tags={"Subscription Plans"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="plans", type="array", @OA\Items(ref="#/components/schemas/SubscriptionPlan"))
     *         )
     *     )
     * )
     */
    public function featured()
    {
        $plans = SubscriptionPlan::active()
            ->featured()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscription-plans/{id}/subscribe",
     *     summary="Subscribe to a plan",
     *     tags={"Subscription Plans"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_method", type="string", example="razorpay"),
     *             @OA\Property(property="payment_id", type="string", example="pay_123456789"),
     *             @OA\Property(property="auto_renew", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="subscription", ref="#/components/schemas/UserSubscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Plan not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or already subscribed"
     *     )
     * )
     */
    public function subscribe(Request $request, $id)
    {
        $user = Auth::user();
        
        $plan = SubscriptionPlan::active()->find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan not found.'
            ], 404);
        }

        // Check if user already has an active subscription
        $activeSubscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription. Please cancel it first.'
            ], 422);
        }

        $validated = $request->validate([
            'payment_method' => 'nullable|string',
            'payment_id' => 'nullable|string',
            'auto_renew' => 'boolean',
        ]);

        // Calculate subscription dates
        $startsAt = now();
        $trialEndsAt = $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null;
        $endsAt = now()->addDays($plan->duration_days);

        // Create subscription
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'vendor_id' => $user->vendor_id ?? null,
            'plan_id' => $plan->id,
            'status' => $plan->price > 0 ? 'pending' : 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'trial_ends_at' => $trialEndsAt,
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_id' => $validated['payment_id'] ?? null,
            'amount_paid' => $plan->discounted_price,
            'currency' => 'INR',
            'auto_renew' => $validated['auto_renew'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully.',
            'subscription' => $subscription->load('plan')
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-subscription",
     *     summary="Get current user's active subscription",
     *     tags={"Subscription Plans"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="subscription", ref="#/components/schemas/UserSubscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active subscription found"
     *     )
     * )
     */
    public function mySubscription()
    {
        $user = Auth::user();
        
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'subscription' => $subscription
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/my-subscription/cancel",
     *     summary="Cancel current subscription",
     *     tags={"Subscription Plans"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Subscription cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active subscription found"
     *     )
     * )
     */
    public function cancelSubscription()
    {
        $user = Auth::user();
        
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.'
            ], 404);
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription-history",
     *     summary="Get user's subscription history",
     *     tags={"Subscription Plans"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="subscriptions", type="array", @OA\Items(ref="#/components/schemas/UserSubscription"))
     *         )
     *     )
     * )
     */
    public function subscriptionHistory()
    {
        $user = Auth::user();
        
        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription-plans/compare",
     *     summary="Compare subscription plans",
     *     tags={"Subscription Plans"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="plans", type="array", @OA\Items(ref="#/components/schemas/SubscriptionPlan"))
     *         )
     *     )
     * )
     */
    public function compare()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price' => $plan->price,
                    'discounted_price' => $plan->discounted_price,
                    'billing_cycle' => $plan->billing_cycle,
                    'billing_cycle_label' => $plan->billing_cycle_label,
                    'duration_days' => $plan->duration_days,
                    'features' => $plan->features,
                    'is_featured' => $plan->is_featured,
                    'trial_days' => $plan->trial_days,
                    'discount_percentage' => $plan->discount_percentage,
                ];
            });

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }
}
