<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\LogsActivity;

class CouponController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of coupons.
     */
    public function index()
    {
        $this->authorize('viewAny', Coupon::class);
        
        $coupons = Coupon::orderBy('created_at', 'desc')->get();
        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        $this->authorize('create', Coupon::class);
        
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Coupon::class);
        
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'description' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        // Convert code to uppercase
        $validated['code'] = strtoupper($validated['code']);
        
        // Set defaults
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['per_user_limit'] = $validated['per_user_limit'] ?? 1;
        $validated['is_active'] = isset($validated['is_active']) ? (bool) $validated['is_active'] : false;

        // Validate percentage doesn't exceed 100
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }

        $coupon = Coupon::create($validated);

        // Log the activity
        $this->logAdminActivity('created', "Created coupon: {$coupon->code} ({$coupon->discount_type}: {$coupon->discount_value})", $coupon);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    /**
     * Display the specified coupon.
     */
    public function show(Coupon $coupon)
    {
        $this->authorize('view', $coupon);
        
        $coupon->load(['usages.user', 'usages.proformaInvoice']);
        return view('admin.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon)
    {
        $this->authorize('update', $coupon);
        
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $this->authorize('update', $coupon);
        
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        // Convert code to uppercase
        $validated['code'] = strtoupper($validated['code']);
        
        // Set defaults
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['per_user_limit'] = $validated['per_user_limit'] ?? 1;
        $validated['is_active'] = isset($validated['is_active']) ? (bool) $validated['is_active'] : false;

        // Validate percentage doesn't exceed 100
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }

        $coupon->update($validated);

        // Log the activity
        $this->logAdminActivity('updated', "Updated coupon: {$coupon->code}", $coupon);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy(Coupon $coupon)
    {
        $this->authorize('delete', $coupon);
        
        $couponCode = $coupon->code;
        $couponId = $coupon->id;

        $coupon->delete();

        // Log the activity
        $this->logAdminActivity('deleted', "Deleted coupon: {$couponCode} (ID: {$couponId})");

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }

    /**
     * Toggle coupon active status.
     */
    public function toggleStatus(Coupon $coupon)
    {
        $this->authorize('update', $coupon);
        
        $coupon->update(['is_active' => !$coupon->is_active]);

        // Log the activity
        $status = $coupon->is_active ? 'activated' : 'deactivated';
        $this->logAdminActivity('updated', "Coupon {$status}: {$coupon->code}", $coupon);

        return response()->json([
            'success' => true,
            'is_active' => $coupon->is_active,
            'message' => $coupon->is_active ? 'Coupon activated.' : 'Coupon deactivated.',
        ]);
    }
}
