<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\Setting;
use App\Models\Referral;
use App\Models\ReferralUser;
use App\Models\Vendor;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Razorpay\Api\Api;

class SubscriptionController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Display subscription plans
     */
    public function plans()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        // Get all active subscription plans
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
        
        // Get current subscription
        $currentSubscription = $vendor->activeSubscription;
        
        // Check if this is first time purchase (no successful subscriptions before)
        $isFirstTimePurchase = !UserSubscription::where('vendor_id', $vendor->id)
            ->whereIn('status', ['active', 'expired', 'cancelled'])
            ->exists();
        
        // Get Razorpay configuration
        $settings = Setting::first();
        $razorpayKeyId = $settings->razorpay_key_id ?? config('services.razorpay.key_id');
        
        return view('vendor.subscription.plans', compact('plans', 'currentSubscription', 'razorpayKeyId', 'isFirstTimePurchase'));
    }

    /**
     * Show current subscription details
     */
    public function current()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        $subscription = $vendor->activeSubscription;
        
        if (!$subscription) {
            return redirect()->route('vendor.subscription.plans')
                ->with('info', 'You do not have an active subscription.');
        }
        
        return view('vendor.subscription.current', compact('subscription'));
    }

    /**
     * Create Razorpay order for subscription
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'referral_code' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $vendor = $user->vendor;
            $plan = SubscriptionPlan::findOrFail($request->plan_id);

            // Check if plan is active
            if (!$plan->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This plan is not available.'
                ], 400);
            }

            // Validate referral code and calculate discount
            $referringVendor = null;
            $referralRecord = null;
            $discountPercentage = 0;
            $originalPrice = $plan->price;
            $finalPrice = $originalPrice;
            
            if ($request->filled('referral_code')) {
                $referralCode = strtoupper(trim($request->referral_code));
                
                // First, check if it's a valid referral code in referrals table (supports both admin-created and vendor codes)
                $referralRecord = Referral::where('referral_code', $referralCode)
                    ->where('status', 'active')
                    ->first();

                if (!$referralRecord) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or inactive referral code.'
                    ], 400);
                }
                
                // If it's a vendor-linked referral, get the vendor
                if ($referralRecord->vendor_id) {
                    $referringVendor = $referralRecord->vendor;
                    
                    // Check if vendor is approved
                    if (!$referringVendor || $referringVendor->status !== 'approved') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Referral code belongs to an inactive vendor.'
                        ], 400);
                    }
                    
                    // Check if vendor is not using their own referral code
                    if ($referringVendor->id === $vendor->id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You cannot use your own referral code.'
                        ], 400);
                    }
                }
                
                // Apply 10% discount
                $discountPercentage = 10;
                $discountAmount = round($originalPrice * $discountPercentage / 100, 2);
                $finalPrice = round($originalPrice - $discountAmount, 2);
            }

            // Get Razorpay credentials from settings
            $settings = Setting::first();
            $keyId = $settings->razorpay_key_id ?? config('services.razorpay.key_id');
            $keySecret = $settings->razorpay_key_secret ?? config('services.razorpay.key_secret');

            if (!$keyId || !$keySecret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured. Please contact administrator.'
                ], 500);
            }

            // Initialize Razorpay API
            $api = new Api($keyId, $keySecret);

            // Create Razorpay order with discounted price
            // Convert to integer paise (Razorpay requires integer amount)
            $amountInPaise = (int) round($finalPrice * 100);
            
            $orderData = [
                'amount' => $amountInPaise, // Amount in paise (must be integer)
                'currency' => 'INR',
                'receipt' => 'subscription_' . $vendor->id . '_' . time(),
                'notes' => [
                    'vendor_id' => $vendor->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'type' => 'subscription',
                    'referral_code' => $referralRecord ? $referralRecord->referral_code : null,
                    'referral_id' => $referralRecord ? $referralRecord->id : null,
                    'original_price' => $originalPrice,
                    'discount_percentage' => $discountPercentage,
                    'final_price' => $finalPrice,
                ]
            ];

            $razorpayOrder = $api->order->create($orderData);

            // Create pending subscription record
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'payment_method' => 'razorpay',
                'payment_id' => $razorpayOrder->id,
                'amount_paid' => $finalPrice,
                'currency' => 'INR',
                'auto_renew' => false,
                'referral_code' => $referralRecord ? $referralRecord->referral_code : null,
                'referral_id' => $referralRecord ? $referralRecord->id : null,
                'notes' => $referralRecord 
                    ? "Applied {$discountPercentage}% discount using referral code: {$referralRecord->referral_code}" 
                    : null,
            ]);

            Log::info('Subscription order created', [
                'vendor_id' => $vendor->id,
                'plan_id' => $plan->id,
                'razorpay_order_id' => $razorpayOrder->id,
                'subscription_id' => $subscription->id,
                'referral_code' => $referralRecord ? $referralRecord->referral_code : null,
                'referral_id' => $referralRecord ? $referralRecord->id : null,
                'original_price' => $originalPrice,
                'discount_percentage' => $discountPercentage,
                'final_price' => $finalPrice,
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $razorpayOrder->id,
                'amount' => $finalPrice,
                'original_amount' => $originalPrice,
                'discount_percentage' => $discountPercentage,
                'discount_applied' => $originalPrice - $finalPrice,
                'currency' => 'INR',
                'subscription_id' => $subscription->id,
                'key_id' => $keyId,
                'plan_name' => $plan->name,
                'vendor_name' => $vendor->store_name,
                'vendor_email' => $vendor->business_email ?? $user->email,
                'vendor_phone' => $vendor->business_phone ?? $user->phone,
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription order. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify Razorpay payment and activate subscription
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
            'subscription_id' => 'required|exists:user_subscriptions,id',
        ]);

        try {
            $user = Auth::user();
            $vendor = $user->vendor;

            // Get subscription
            $subscription = UserSubscription::findOrFail($request->subscription_id);

            // Verify subscription belongs to this vendor
            if ($subscription->vendor_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subscription.'
                ], 403);
            }

            // Get Razorpay credentials
            $settings = Setting::first();
            $keyId = $settings->razorpay_key_id ?? config('services.razorpay.key_id');
            $keySecret = $settings->razorpay_key_secret ?? config('services.razorpay.key_secret');

            // Initialize Razorpay API
            $api = new Api($keyId, $keySecret);

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Get plan details
            $plan = $subscription->plan;

            // Calculate subscription dates
            $startsAt = now();
            $endsAt = now()->addDays($plan->duration_days);
            
            // If plan has trial period and this is first subscription
            $trialEndsAt = null;
            if ($plan->trial_days > 0 && !$vendor->subscriptions()->where('status', 'active')->exists()) {
                $trialEndsAt = now()->addDays($plan->trial_days);
            }

            // Update subscription
            $subscription->update([
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'trial_ends_at' => $trialEndsAt,
                'payment_id' => $request->razorpay_payment_id,
            ]);

            // Cancel any other active subscriptions for this vendor
            UserSubscription::where('vendor_id', $vendor->id)
                ->where('id', '!=', $subscription->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            // Create referral user entry if referral code was used
            if ($subscription->referral_id) {
                // Get the referral record to check if it's vendor-linked or admin-created
                $referralRecord = Referral::find($subscription->referral_id);
                
                if ($referralRecord) {
                    // Only create ReferralUser for non-vendor (admin-created) referral codes
                    // Vendor referrals are tracked in ReferralEarning table
                    if (!$referralRecord->vendor_id) {
                        ReferralUser::create([
                            'referral_id' => $subscription->referral_id,
                            'user_id' => $user->id,
                            'name' => $vendor->store_name ?? $user->name,
                            'email' => $vendor->business_email ?? $user->email,
                            'phone_number' => $vendor->business_phone ?? $user->phone,
                            'notes' => 'Subscription purchase - Plan: ' . $plan->name,
                            'payment_status' => 'pending',
                            'payment_amount' => 0, // Admin will set the referral amount
                        ]);

                        Log::info('Referral user created for admin referral code', [
                            'referral_id' => $subscription->referral_id,
                            'referral_code' => $subscription->referral_code,
                            'user_id' => $user->id,
                            'vendor_id' => $vendor->id,
                        ]);
                    }
                }
            }

            // Record referral earning if vendor referral code was used
            if ($subscription->referral_code && $subscription->referral_id) {
                // Get the referral record
                $referralRecord = Referral::find($subscription->referral_id);
                
                // Check if it's a vendor-linked referral code
                if ($referralRecord && $referralRecord->vendor_id) {
                    $referringVendor = $referralRecord->vendor;
                    
                    if ($referringVendor && $referringVendor->status === 'approved') {
                        // Calculate commission (10% of amount paid)
                        $commissionPercentage = 10;
                        $commissionAmount = round($subscription->amount_paid * $commissionPercentage / 100, 2);
                        
                        // Create referral earning record
                        \App\Models\ReferralEarning::create([
                            'referrer_vendor_id' => $referringVendor->id,
                            'referred_vendor_id' => $vendor->id,
                            'subscription_id' => $subscription->id,
                            'referral_code' => $subscription->referral_code,
                            'subscription_amount' => $subscription->amount_paid,
                            'commission_percentage' => $commissionPercentage,
                            'commission_amount' => $commissionAmount,
                            'status' => 'pending', // Admin needs to approve
                        ]);
                        
                        Log::info('Referral earning recorded for vendor referral code', [
                            'referrer_vendor_id' => $referringVendor->id,
                            'referred_vendor_id' => $vendor->id,
                            'commission_amount' => $commissionAmount,
                            'subscription_id' => $subscription->id,
                        ]);
                    }
                }
            }

            Log::info('Subscription activated', [
                'vendor_id' => $vendor->id,
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'payment_id' => $request->razorpay_payment_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully!',
                'redirect_url' => route('vendor.dashboard')
            ]);

        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Razorpay signature verification failed', [
                'error' => $e->getMessage(),
                'subscription_id' => $request->subscription_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Subscription verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment. Please contact support.'
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        try {
            $user = Auth::user();
            $vendor = $user->vendor;

            $subscription = $vendor->activeSubscription;

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found.'
                ], 404);
            }

            // Update subscription status
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);

            Log::info('Subscription cancelled', [
                'vendor_id' => $vendor->id,
                'subscription_id' => $subscription->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription.'
            ], 500);
        }
    }

    /**
     * Get subscription history
     */
    public function history()
    {
        $user = Auth::user();
        $vendor = $user->vendor;

        $subscriptions = UserSubscription::where('vendor_id', $vendor->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('vendor.subscription.history', compact('subscriptions'));
    }
}
