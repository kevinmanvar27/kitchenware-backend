<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Traits\LogsActivity;

class SubscriptionPlanController extends Controller
{
    use LogsActivity;
    /**
     * Get all subscription plans for settings page
     */
    public function index()
    {
        $plans = SubscriptionPlan::ordered()->get();
        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }

    /**
     * Store a new subscription plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (SubscriptionPlan::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        // Set defaults
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['sort_order'] = $validated['sort_order'] ?? SubscriptionPlan::max('sort_order') + 1;
        $validated['trial_days'] = $validated['trial_days'] ?? 0;
        $validated['discount_percentage'] = $validated['discount_percentage'] ?? 0;
        
        // Set limits to null (no limits)
        $validated['max_products'] = null;
        $validated['max_vendors'] = null;
        $validated['max_customers'] = null;
        $validated['max_invoices_per_month'] = null;
        $validated['storage_limit_mb'] = null;

        $plan = SubscriptionPlan::create($validated);

        // Log the activity
        $this->logAdminActivity('created', "Created subscription plan: {$plan->name} (₹{$plan->price}/{$plan->billing_cycle})", $plan);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan created successfully.',
            'plan' => $plan
        ]);
    }

    /**
     * Update a subscription plan
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $plan->name) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (SubscriptionPlan::where('slug', $slug)->where('id', '!=', $plan->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        
        // Set limits to null (no limits)
        $validated['max_products'] = null;
        $validated['max_vendors'] = null;
        $validated['max_customers'] = null;
        $validated['max_invoices_per_month'] = null;
        $validated['storage_limit_mb'] = null;

        $plan->update($validated);

        // Log the activity
        $this->logAdminActivity('updated', "Updated subscription plan: {$plan->name}", $plan);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated successfully.',
            'plan' => $plan->fresh()
        ]);
    }

    /**
     * Delete a subscription plan
     */
    public function destroy(SubscriptionPlan $plan)
    {
        // Check if plan has active subscriptions
        $activeSubscriptions = UserSubscription::where('plan_id', $plan->id)
            ->where('status', 'active')
            ->count();

        if ($activeSubscriptions > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this plan. It has {$activeSubscriptions} active subscription(s)."
            ], 422);
        }

        $planName = $plan->name;
        $planId = $plan->id;

        $plan->delete();

        // Log the activity
        $this->logAdminActivity('deleted', "Deleted subscription plan: {$planName} (ID: {$planId})");

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan deleted successfully.'
        ]);
    }

    /**
     * Toggle plan active status
     */
    public function toggleStatus(SubscriptionPlan $plan)
    {
        $plan->is_active = !$plan->is_active;
        $plan->save();

        // Log the activity
        $status = $plan->is_active ? 'activated' : 'deactivated';
        $this->logAdminActivity($status, "Subscription plan {$status}: {$plan->name}", $plan);

        return response()->json([
            'success' => true,
            'message' => 'Plan status updated successfully.',
            'is_active' => $plan->is_active
        ]);
    }

    /**
     * Update plan sort order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'plans' => 'required|array',
            'plans.*.id' => 'required|exists:subscription_plans,id',
            'plans.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->plans as $planData) {
            SubscriptionPlan::where('id', $planData['id'])
                ->update(['sort_order' => $planData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Plan order updated successfully.'
        ]);
    }

    /**
     * Get subscription statistics
     */
    public function statistics()
    {
        $stats = [
            'total_plans' => SubscriptionPlan::count(),
            'active_plans' => SubscriptionPlan::where('is_active', true)->count(),
            'total_subscriptions' => UserSubscription::count(),
            'active_subscriptions' => UserSubscription::where('status', 'active')->count(),
            'revenue_this_month' => UserSubscription::where('status', 'active')
                ->whereMonth('created_at', now()->month)
                ->sum('amount_paid'),
            'subscriptions_by_plan' => SubscriptionPlan::withCount(['subscriptions' => function ($query) {
                $query->where('status', 'active');
            }])->get()->map(function ($plan) {
                return [
                    'name' => $plan->name,
                    'count' => $plan->subscriptions_count
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }
}
