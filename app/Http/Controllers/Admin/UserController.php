<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class UserController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of all users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Show only regular users
        $users = User::where('user_role', 'user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
        return view('admin.users.index', compact('users'));
    }
    
    /**
     * Display a listing of staff members (admin, super_admin, editor).
     *
     * @return \Illuminate\View\View
     */
    public function staff()
    {
        // Fetch all users except those with the 'user' role, with their active salary
        $staff = User::where('user_role', '!=', 'user')
                    ->with(['salaries' => function ($query) {
                        $query->where('is_active', true)->orderBy('effective_from', 'desc');
                    }])
                    ->orderBy('user_role')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('admin.users.staff', compact('staff'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $role = request('role', 'user');
        
        // If role is 'staff', default to 'admin' for the form
        // 'staff' is a grouping term, not an actual role
        if ($role === 'staff') {
            $role = 'admin';
        }
        
        // Ensure the role is valid
        $validRoles = ['super_admin', 'admin', 'editor', 'user'];
        if (!in_array($role, $validRoles)) {
            $role = 'user';
        }
        
        // Determine if we're creating a staff member
        $isStaff = request('role') === 'staff';
        
        $roles = Role::all();
        return view('admin.users.create', compact('role', 'roles', 'isStaff'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_role' => ['required', 'string', Rule::in(['super_admin', 'admin', 'editor', 'user'])],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'discount_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'status' => ['nullable', 'string', Rule::in(['Pending', 'Under review', 'Approved', 'Suspend', 'Block'])],
        ]);

        // Get the frontend access permission setting
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_role' => $request->user_role,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'mobile_number' => $request->mobile_number,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'status' => $request->status ?? 'Approved',
            // Users created by admin are auto-approved
            'is_approved' => true
        ]);

        // Assign role to user through the pivot table
        $role = Role::where('name', $request->user_role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        $this->handleAvatarUpload($request, $user);

        // Log the activity
        $this->logAdminActivity('created', "Created user: {$user->name} ({$user->user_role})", $user);

        // Redirect to appropriate page based on user role
        if ($user->hasAnyRole(['user'])) {
            return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
        } else {
            return redirect()->route('admin.users.staff')->with('success', 'Staff member created successfully.');
        }
    }

    /**
     * Display the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        // Check if the request is an AJAX request for modal content
        if (request()->ajax()) {
            // Return only the partial view for modals
            return view('admin.users._user_details', compact('user'));
        }
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'user_role' => ['required', 'string', Rule::in(['super_admin', 'admin', 'editor', 'user'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'discount_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'status' => ['nullable', 'string', Rule::in(['Pending', 'Under review', 'Approved', 'Suspend', 'Block'])],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->user_role = $request->user_role;
        $user->date_of_birth = $request->date_of_birth;
        $user->address = $request->address;
        $user->mobile_number = $request->mobile_number;
        $user->discount_percentage = $request->discount_percentage ?? $user->discount_percentage;
        $user->status = $request->status ?? $user->status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Update role assignment through the pivot table
        $role = Role::where('name', $request->user_role)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        $this->handleAvatarUpload($request, $user);

        // Log the activity
        $this->logAdminActivity('updated', "Updated user: {$user->name} ({$user->user_role})", $user);

        // Redirect to appropriate page based on user role
        if ($user->hasAnyRole(['user'])) {
            return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
        } else {
            return redirect()->route('admin.users.staff')->with('success', 'Staff member updated successfully.');
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Prevent users from deleting themselves
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Store user info before deletion for logging
        $userName = $user->name;
        $userRole = $user->user_role;
        $userId = $user->id;

        // Delete user avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $user->delete();

        // Log the activity after successful deletion
        $this->logAdminActivity('deleted', "Deleted user: {$userName} ({$userRole}, ID: {$userId})");

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * Handle user avatar upload
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return void
     */
    private function handleAvatarUpload(Request $request, User $user)
    {
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            // Store new avatar
            $filename = uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension();
            $path = $request->file('avatar')->storeAs('avatars', $filename, 'public');
            $user->avatar = $filename;
            $user->save();
        }
    }

    /**
     * Approve a user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(User $user)
    {
        // Only allow approving users with 'user' role
        if ($user->user_role !== 'user') {
            return redirect()->back()->with('error', 'Only regular users can be approved.');
        }

        $user->is_approved = true;
        $user->save();

        return redirect()->back()->with('success', 'User approved successfully.');
    }
    
    /**
     * Disapprove a user (revoke access).
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disapprove(User $user)
    {
        // Only allow disapproving users with 'user' role
        if ($user->user_role !== 'user') {
            return redirect()->back()->with('error', 'Only regular users can be disapproved.');
        }

        $user->is_approved = false;
        $user->save();

        return redirect()->back()->with('success', 'User access has been revoked.');
    }

    /**
     * Change user status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(['Pending', 'Under review', 'Approved', 'Suspend', 'Block'])],
        ]);

        $oldStatus = $user->status;
        $user->status = $request->status;
        $user->save();

        // Log the activity
        $this->logAdminActivity('updated', "Changed user status from '{$oldStatus}' to '{$request->status}' for: {$user->name}", $user);

        return redirect()->back()->with('success', "User status changed to {$request->status} successfully.");
    }
}