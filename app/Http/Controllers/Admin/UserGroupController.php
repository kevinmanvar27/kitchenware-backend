<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserGroup;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Traits\LogsActivity;

class UserGroupController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of user groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $userGroups = UserGroup::orderBy('created_at', 'desc')->get();
        return view('admin.user-groups.index', compact('userGroups'));
    }

    /**
     * Show the form for creating a new user group.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::where('user_role', 'user')->with('userGroups')->orderBy('name')->get();
        return view('admin.user-groups.create', compact('users'));
    }

    /**
     * Store a newly created user group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:user_groups'],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['required', 'numeric', 'between:0,100'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'force_transfer' => ['nullable'],
        ]);

        $userGroup = UserGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'discount_percentage' => $request->discount_percentage,
        ]);

        // Attach selected users to the group
        if ($request->has('users') && is_array($request->users) && count($request->users) > 0) {
            // If force_transfer is set (either "1" or true), remove users from their existing groups
            $forceTransfer = $request->input('force_transfer');
            
            \Log::info('UserGroup Store - Force Transfer Value:', [
                'force_transfer' => $forceTransfer,
                'type' => gettype($forceTransfer),
                'users' => $request->users
            ]);
            
            if ($forceTransfer == '1' || $forceTransfer === true || $forceTransfer === 1) {
                \Log::info('UserGroup Store - Detaching users from existing groups');
                
                foreach ($request->users as $userId) {
                    // Detach user from all existing groups
                    $user = User::find($userId);
                    if ($user) {
                        $existingGroups = $user->userGroups()->pluck('name')->toArray();
                        \Log::info("Detaching user {$userId} from groups:", $existingGroups);
                        $user->userGroups()->detach();
                    }
                }
            }
            
            \Log::info('UserGroup Store - Attaching users to new group:', ['group_id' => $userGroup->id, 'users' => $request->users]);
            $userGroup->users()->attach($request->users);
        }

        // Log the activity
        $userCount = count($request->users ?? []);
        $this->logAdminActivity('created', "Created user group: {$userGroup->name} ({$userCount} users, {$userGroup->discount_percentage}% discount)", $userGroup);

        return redirect()->route('admin.user-groups.index')->with('success', 'User group created successfully.');
    }

    /**
     * Display the specified user group.
     *
     * @param  \App\Models\UserGroup  $userGroup
     * @return \Illuminate\View\View
     */
    public function show(UserGroup $userGroup)
    {
        $userGroup->load('users');
        
        // Check if the request is an AJAX request for modal content
        if (request()->ajax()) {
            // Return only the partial view for modals
            return view('admin.user-groups._group_details', compact('userGroup'));
        }
        
        return view('admin.user-groups.show', compact('userGroup'));
    }

    /**
     * Show the form for editing the specified user group.
     *
     * @param  \App\Models\UserGroup  $userGroup
     * @return \Illuminate\View\View
     */
    public function edit(UserGroup $userGroup)
    {
        $userGroup->load('users');
        $users = User::where('user_role', 'user')->with('userGroups')->orderBy('name')->get();
        $selectedUsers = $userGroup->users->pluck('id')->toArray();
        
        // Check if the request is an AJAX request for modal content
        if (request()->ajax()) {
            // Return only the partial view for modals
            return view('admin.user-groups._group_edit', compact('userGroup', 'users', 'selectedUsers'));
        }
        
        return view('admin.user-groups.edit', compact('userGroup', 'users', 'selectedUsers'));
    }

    /**
     * Update the specified user group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserGroup  $userGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, UserGroup $userGroup)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('user_groups')->ignore($userGroup->id)],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['required', 'numeric', 'between:0,100'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'force_transfer' => ['nullable'],
        ]);

        $userGroup->update([
            'name' => $request->name,
            'description' => $request->description,
            'discount_percentage' => $request->discount_percentage,
        ]);

        // Sync selected users with the group
        $forceTransfer = $request->input('force_transfer');
        
        \Log::info('UserGroup Update - Force Transfer Value:', [
            'force_transfer' => $forceTransfer,
            'type' => gettype($forceTransfer),
            'users' => $request->users ?? [],
            'group_id' => $userGroup->id
        ]);
        
        if ($request->has('users') && is_array($request->users)) {
            // If force_transfer is set (either "1" or true), remove users from their existing groups (except current group)
            if ($forceTransfer == '1' || $forceTransfer === true || $forceTransfer === 1) {
                \Log::info('UserGroup Update - Detaching users from other groups');
                
                foreach ($request->users as $userId) {
                    $user = User::find($userId);
                    if ($user) {
                        $existingGroups = $user->userGroups()->where('user_groups.id', '!=', $userGroup->id)->pluck('name')->toArray();
                        if (count($existingGroups) > 0) {
                            \Log::info("Detaching user {$userId} from other groups:", $existingGroups);
                        }
                        // Detach from all groups except the current one
                        $user->userGroups()->where('user_groups.id', '!=', $userGroup->id)->detach();
                    }
                }
            }
            
            \Log::info('UserGroup Update - Syncing users:', ['group_id' => $userGroup->id, 'users' => $request->users]);
            $userGroup->users()->sync($request->users);
        } else {
            $userGroup->users()->sync([]);
        }

        // Log the activity
        $this->logAdminActivity('updated', "Updated user group: {$userGroup->name}", $userGroup);

        // Check if the request is an AJAX request
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'User group updated successfully.']);
        }

        return redirect()->route('admin.user-groups.index')->with('success', 'User group updated successfully.');
    }

    /**
     * Remove the specified user group from storage.
     *
     * @param  \App\Models\UserGroup  $userGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(UserGroup $userGroup)
    {
        $groupName = $userGroup->name;
        $groupId = $userGroup->id;

        $userGroup->delete();

        // Log the activity
        $this->logAdminActivity('deleted', "Deleted user group: {$groupName} (ID: {$groupId})");

        // Check if the request is an AJAX request
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'User group deleted successfully.']);
        }

        return redirect()->route('admin.user-groups.index')->with('success', 'User group deleted successfully.');
    }

    /**
     * Check if users are already in other groups (AJAX endpoint)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUserGroups(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        $currentGroupId = $request->input('current_group_id', null);
        
        $conflicts = [];
        
        foreach ($userIds as $userId) {
            $user = User::with('userGroups')->find($userId);
            
            if ($user && $user->userGroups->count() > 0) {
                // Filter out the current group if editing
                $existingGroups = $user->userGroups->filter(function($group) use ($currentGroupId) {
                    return $group->id != $currentGroupId;
                });
                
                if ($existingGroups->count() > 0) {
                    $conflicts[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'existing_group' => $existingGroups->first()->name,
                        'existing_group_id' => $existingGroups->first()->id,
                    ];
                }
            }
        }
        
        return response()->json([
            'has_conflicts' => count($conflicts) > 0,
            'conflicts' => $conflicts
        ]);
    }
}