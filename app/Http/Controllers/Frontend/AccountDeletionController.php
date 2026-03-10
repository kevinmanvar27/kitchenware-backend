<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AccountDeletionController extends Controller
{
    /**
     * Show the account deletion form
     * 
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        return view('frontend.auth.delete-account');
    }

    /**
     * Process account deletion request
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'reason' => 'nullable|string|max:500',
            'confirm_deletion' => 'required|accepted',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'confirm_deletion.required' => 'You must confirm that you want to delete your account.',
            'confirm_deletion.accepted' => 'You must confirm that you want to delete your account.',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.'])->withInput();
        }

        // Check if account is already deleted/anonymized
        if (str_starts_with($user->email, 'deleted_') && str_ends_with($user->email, '@deleted.local')) {
            return back()->withErrors(['email' => 'This account has already been deleted.'])->withInput();
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.'])->withInput();
        }

        // Log deletion reason if provided
        if ($request->reason) {
            Log::info('Account deletion via web form', [
                'user_id' => $user->id,
                'email' => $user->email,
                'reason' => $request->reason,
                'deleted_at' => now(),
            ]);
        }

        // Delete related data
        // 1. Delete cart items
        \App\Models\ShoppingCartItem::where('user_id', $user->id)->delete();

        // 2. Delete wishlist items
        \App\Models\Wishlist::where('user_id', $user->id)->delete();

        // 3. Delete notifications
        $user->notifications()->delete();

        // 4. Delete avatar file if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        // 5. Revoke all tokens (logout from all devices)
        $user->tokens()->delete();

        // 6. Anonymize user data (for data retention compliance - keeps record for invoice history)
        $user->update([
            'name' => 'Deleted User',
            'email' => 'deleted_' . $user->id . '_' . time() . '@deleted.local',
            'mobile_number' => null,
            'address' => null,
            'avatar' => null,
            'date_of_birth' => null,
            'is_approved' => false,
            'password' => Hash::make(\Str::random(32)),
        ]);

        return redirect()->route('account.delete.form')
            ->with('success', 'Your account has been successfully deleted. All your personal data has been removed from our system.');
    }
}
