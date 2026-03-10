<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProformaInvoice;
use App\Models\VendorCustomer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class CustomerController extends Controller
{
    use LogsActivity;
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of customers for the vendor.
     * Shows both:
     * 1. Customers created by vendor with login credentials
     * 2. Customers who have sent invoices to this vendor
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        // Get vendor-created customers with login credentials
        $query = VendorCustomer::where('vendor_id', $vendor->id)
            ->whereNotNull('email');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $customers = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Calculate statistics
        $totalCustomers = VendorCustomer::where('vendor_id', $vendor->id)
            ->whereNotNull('email')
            ->count();
        
        $activeCustomers = VendorCustomer::where('vendor_id', $vendor->id)
            ->whereNotNull('email')
            ->where('is_active', true)
            ->count();
        
        // Get new customers this month
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $newCustomersThisMonth = VendorCustomer::where('vendor_id', $vendor->id)
            ->whereNotNull('email')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
        
        return view('vendor.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'newCustomersThisMonth'
        ));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('vendor.customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'mobile_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);
        
        // Check if email already exists for this vendor
        $existingCustomer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('email', $request->email)
            ->first();
        
        if ($existingCustomer) {
            return back()->withErrors(['email' => 'A customer with this email already exists.'])->withInput();
        }
        
        $customer = VendorCustomer::create([
            'vendor_id' => $vendor->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile_number' => $request->mobile_number,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'is_active' => true,
        ]);
        
        // Log the activity
        $this->logVendorActivity($vendor->id, 'created', "Created customer: {$customer->name} ({$customer->email})", $customer);
        
        return redirect()->route('vendor.customers.show', $customer->id)
            ->with('success', 'Customer created successfully. Login credentials: Email: ' . $customer->email . ', Password: ' . $request->password);
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        
        // Get vendor customer
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        // Get customer's orders from this vendor
        $orders = ProformaInvoice::where('vendor_id', $vendor->id)
            ->where('vendor_customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Calculate customer statistics
        $totalOrders = ProformaInvoice::where('vendor_id', $vendor->id)
            ->where('vendor_customer_id', $customer->id)
            ->count();
            
        $totalSpent = ProformaInvoice::where('vendor_id', $vendor->id)
            ->where('vendor_customer_id', $customer->id)
            ->sum('total_amount');
        
        $customerSince = $customer->created_at;
        
        return view('vendor.customers.show', compact(
            'customer',
            'orders',
            'totalOrders',
            'totalSpent',
            'customerSince'
        ));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        $vendor = $this->getVendor();
        
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        return view('vendor.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);
        
        $customer->update([
            'name' => $request->name,
            'mobile_number' => $request->mobile_number,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);
        
        // If deactivating, revoke all tokens
        if (!$customer->is_active) {
            $customer->tokens()->delete();
        }
        
        // Log the activity
        $this->logVendorActivity($vendor->id, 'updated', "Updated customer: {$customer->name}", $customer);
        
        return redirect()->route('vendor.customers.show', $customer->id)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Reset customer password.
     */
    public function resetPassword(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $request->validate([
            'password' => 'required|string|min:6',
        ]);
        
        $customer->update([
            'password' => Hash::make($request->password)
        ]);
        
        // Revoke all existing tokens
        $customer->tokens()->delete();
        
        // Log the activity (sensitive action)
        $this->logVendorActivity($vendor->id, 'password_reset', "Reset password for customer: {$customer->name} ({$customer->email})", $customer);
        
        return back()->with('success', 'Password reset successfully. New password: ' . $request->password);
    }

    /**
     * Toggle customer status (active/inactive).
     */
    public function toggleStatus($id)
    {
        $vendor = $this->getVendor();
        
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $customer->update([
            'is_active' => !$customer->is_active
        ]);
        
        // If deactivating, revoke all tokens
        if (!$customer->is_active) {
            $customer->tokens()->delete();
        }
        
        $status = $customer->is_active ? 'activated' : 'deactivated';
        
        // Log the activity
        $this->logVendorActivity($vendor->id, $status, "Customer {$status}: {$customer->name}", $customer);
        
        return back()->with('success', "Customer {$status} successfully.");
    }

    /**
     * Remove the specified customer.
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        // Revoke all tokens
        $customer->tokens()->delete();
        
        $customerName = $customer->name;
        $customerId = $customer->id;
        
        // Delete the customer
        $customer->delete();
        
        // Log the activity
        $this->logVendorActivity($vendor->id, 'deleted', "Deleted customer: {$customerName} (ID: {$customerId})");
        
        return redirect()->route('vendor.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Upload customer profile avatar.
     */
    public function uploadAvatar(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Delete old avatar if exists
        if ($customer->profile_avatar) {
            Storage::disk('public')->delete('customer-avatars/' . $vendor->id . '/' . $customer->profile_avatar);
        }
        
        // Store new avatar
        $file = $request->file('avatar');
        $filename = 'avatar_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('customer-avatars/' . $vendor->id, $filename, 'public');
        
        // Update customer record
        $customer->update(['profile_avatar' => $filename]);
        
        return back()->with('success', 'Profile avatar uploaded successfully.');
    }

    /**
     * Remove customer profile avatar.
     */
    public function removeAvatar($id)
    {
        $vendor = $this->getVendor();
        
        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->firstOrFail();
        
        // Delete avatar file if exists
        if ($customer->profile_avatar) {
            Storage::disk('public')->delete('customer-avatars/' . $vendor->id . '/' . $customer->profile_avatar);
            $customer->update(['profile_avatar' => null]);
        }
        
        return back()->with('success', 'Profile avatar removed successfully.');
    }
}
