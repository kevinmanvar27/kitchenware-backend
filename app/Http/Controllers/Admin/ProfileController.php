<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show the user profile form.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update the user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Check if password is being changed
        $changingPassword = $request->filled('password');
        
        // Prepare validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
        ];
        
        // Add password-related validation only if password is being changed
        if ($changingPassword) {
            $rules['current_password'] = ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed', 'different:current_password'];
        }
        
        // Custom error messages
        $messages = [
            'password.different' => 'The new password must be different from your current password.',
        ];
        
        // Validate the request
        $request->validate($rules, $messages);

        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->date_of_birth = $request->date_of_birth;

        // Update password only if it's being changed
        if ($changingPassword) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Update the user's avatar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAvatar(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Validate only the avatar field
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
        ], [
            'avatar.required' => 'Please select an image to upload.',
            'avatar.image' => 'The file must be an image.',
            'avatar.max' => 'The image may not be greater than 2MB.',
        ]);

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }
        
        // Store new avatar
        $avatarName = time() . '_' . $user->id . '.' . $request->file('avatar')->extension();
        $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
        $user->avatar = $avatarName;
        $user->save();

        return redirect()->route('admin.profile')->with('success', 'Profile picture updated successfully.');
    }
    
    /**
     * Remove the user's avatar.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeAvatar()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Delete avatar file if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->avatar = null;
            $user->save();
        }
        
        return redirect()->route('admin.profile')->with('success', 'Profile picture removed successfully.');
    }
}