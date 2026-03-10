<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class StaffController extends Controller
{
    use LogsActivity;
    /**
     * Available permissions for vendor staff
     */
    public static $availablePermissions = [
        'dashboard' => 'Dashboard',
        'profile' => 'Profile',
        'store_settings' => 'Store Settings',
        'products' => 'Products',
        'variations' => 'Product Variations',
        'attributes' => 'Product Attributes',
        'categories' => 'Categories',
        'invoices' => 'Invoices',
        'pending_bills' => 'Pending Bills',
        'leads' => 'Leads',
        'customers' => 'Customers',
        'staff' => 'Staff Management',
        'salary' => 'Salary Management',
        'attendance' => 'Attendance',
        'reports' => 'Reports',
        'analytics' => 'Product Analytics',
        'coupons' => 'Coupons',
        'banners' => 'Banners',
        'push_notifications' => 'Push Notifications',
        'activity_logs' => 'Activity Logs',
        'view_tasks' => 'View Tasks',
    ];

    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        $user = Auth::user();
        
        if ($user->isVendor()) {
            return $user->vendor;
        }
        
        if ($user->isVendorStaff()) {
            return $user->vendorStaff?->vendor;
        }
        
        return null;
    }
    
    /**
     * Check if current user can manage staff
     */
    private function canManageStaff()
    {
        $user = Auth::user();
        
        // Vendor owners can always manage staff
        if ($user->isVendor()) {
            return true;
        }
        
        // Staff can only manage if they have staff permission
        if ($user->isVendorStaff()) {
            return $user->vendorStaff?->hasPermission('staff');
        }
        
        return false;
    }

    /**
     * Display a listing of the staff.
     */
    public function index()
    {
        if (!$this->canManageStaff()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to manage staff.');
        }
        
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('vendor.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        if (!$this->canManageStaff()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to create staff.');
        }
        
        $permissions = self::$availablePermissions;
        return view('vendor.staff.create', compact('permissions'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        if (!$this->canManageStaff()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to create staff.');
        }
        
        $vendor = $this->getVendor();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'required|string|max:50',
            'permissions' => 'nullable|array',
        ]);

        DB::beginTransaction();
        
        try {
            // Create user account with vendor_staff role
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile_number' => $request->mobile_number,
                'user_role' => 'vendor_staff', // Important: Set the correct user_role
                'is_approved' => true,
            ]);

            // Create vendor staff record
            VendorStaff::create([
                'vendor_id' => $vendor->id,
                'user_id' => $user->id,
                'role' => $request->role,
                'permissions' => $request->permissions ?? [],
                'is_active' => true,
            ]);

            DB::commit();

            // Log the activity
            $this->logVendorActivity($vendor->id, 'created', "Created staff member: {$user->name} ({$request->role})", $user);

            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff member added successfully. They can now login using their email and password.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating staff member: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        if (!$this->canManageStaff()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to view staff details.');
        }
        
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $permissions = self::$availablePermissions;
        
        return view('vendor.staff.show', compact('staff', 'permissions'));
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit($id)
    {
        if (!$this->canManageStaff()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to edit staff.');
        }
        
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $permissions = self::$availablePermissions;
        
        return view('vendor.staff.edit', compact('staff', 'permissions'));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        if (!$this->canManageStaff()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to update staff.');
        }
        
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'required|string|max:50',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            // Update user account
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
            ];
            
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            
            $staff->user->update($userData);

            // Update vendor staff record
            $staff->update([
                'role' => $request->role,
                'permissions' => $request->permissions ?? [],
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            // Log the activity
            $this->logVendorActivity($vendor->id, 'updated', "Updated staff member: {$staff->user->name} ({$staff->role})", $staff->user);

            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff member updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating staff member: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy($id)
    {
        if (!$this->canManageStaff()) {
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to delete staff.');
        }
        
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $staffName = $staff->user->name;
        $staffId = $staff->user_id;
        
        DB::beginTransaction();
        
        try {
            // Delete the user account
            $staff->user->delete();
            
            // Delete the vendor staff record
            $staff->delete();

            DB::commit();

            // Log the activity
            $this->logVendorActivity($vendor->id, 'deleted', "Deleted staff member: {$staffName} (ID: {$staffId})");

            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff member removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error removing staff member: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle staff active status.
     */
    public function toggleStatus($id)
    {
        if (!$this->canManageStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage staff.'
            ], 403);
        }
        
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $staff->update(['is_active' => !$staff->is_active]);

        // Log the activity
        $status = $staff->is_active ? 'activated' : 'deactivated';
        $this->logVendorActivity($vendor->id, $status, "Staff member {$status}: {$staff->user->name}", $staff->user);

        return response()->json([
            'success' => true,
            'is_active' => $staff->is_active,
            'message' => $staff->is_active ? 'Staff member activated.' : 'Staff member deactivated.'
        ]);
    }
}
