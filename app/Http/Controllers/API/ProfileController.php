<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Profile",
 *     description="API Endpoints for User Profile Management"
 * )
 */
class ProfileController extends ApiController
{
    /**
     * Get authenticated user's profile
     * 
     * @OA\Get(
     *      path="/api/v1/profile",
     *      operationId="getProfile",
     *      tags={"Profile"},
     *      summary="Get user profile",
     *      description="Returns the authenticated user's profile data",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        // Add avatar URL if exists
        $userData = $user->toArray();
        $userData['avatar_url'] = $user->avatar ? asset('storage/avatars/' . $user->avatar) : null;
        
        return $this->sendResponse($userData, 'Profile retrieved successfully.');
    }

    /**
     * Update authenticated user's profile
     * 
     * @OA\Put(
     *      path="/api/v1/profile",
     *      operationId="updateProfile",
     *      tags={"Profile"},
     *      summary="Update user profile",
     *      description="Update the authenticated user's profile data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *              @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *              @OA\Property(property="address", type="string", example="123 Main St"),
     *              @OA\Property(property="mobile_number", type="string", example="1234567890"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'mobile_number' => 'nullable|string|max:20',
        ]);

        $user->update($request->only([
            'name',
            'email',
            'date_of_birth',
            'address',
            'mobile_number',
        ]));

        // Add avatar URL if exists
        $userData = $user->fresh()->toArray();
        $userData['avatar_url'] = $user->avatar ? asset('storage/avatars/' . $user->avatar) : null;

        return $this->sendResponse($userData, 'Profile updated successfully.');
    }

    /**
     * Upload/update user avatar
     * 
     * @OA\Post(
     *      path="/api/v1/profile/avatar",
     *      operationId="updateAvatar",
     *      tags={"Profile"},
     *      summary="Upload user avatar",
     *      description="Upload or update the authenticated user's avatar",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="avatar",
     *                      type="string",
     *                      format="binary",
     *                      description="Avatar image file"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        // Store new avatar
        $avatarName = time() . '_' . $user->id . '.' . $request->avatar->extension();
        $request->avatar->storeAs('avatars', $avatarName, 'public');

        $user->update(['avatar' => $avatarName]);

        return $this->sendResponse([
            'avatar' => $avatarName,
            'avatar_url' => asset('storage/avatars/' . $avatarName),
        ], 'Avatar updated successfully.');
    }

    /**
     * Remove user avatar
     * 
     * @OA\Delete(
     *      path="/api/v1/profile/avatar",
     *      operationId="removeAvatar",
     *      tags={"Profile"},
     *      summary="Remove user avatar",
     *      description="Remove the authenticated user's avatar",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->update(['avatar' => null]);
        }

        return $this->sendResponse(null, 'Avatar removed successfully.');
    }

    /**
     * Change user password
     * 
     * @OA\Put(
     *      path="/api/v1/profile/password",
     *      operationId="changePassword",
     *      tags={"Profile"},
     *      summary="Change user password",
     *      description="Change the authenticated user's password",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"current_password","password","password_confirmation"},
     *              @OA\Property(property="current_password", type="string", format="password", example="OldPassword123"),
     *              @OA\Property(property="password", type="string", format="password", example="NewPassword123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword123"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Validation Error', ['current_password' => ['The current password is incorrect.']], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->sendResponse(null, 'Password changed successfully.');
    }

    /**
     * Delete user account
     * 
     * @OA\Delete(
     *      path="/api/v1/profile/delete-account",
     *      operationId="deleteAccount",
     *      tags={"Profile"},
     *      summary="Delete user account",
     *      description="Permanently delete the authenticated user's account. This action cannot be undone. All user data including cart items, wishlist, and notifications will be deleted.",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"password"},
     *              @OA\Property(property="password", type="string", format="password", example="CurrentPassword123", description="Current password for verification"),
     *              @OA\Property(property="reason", type="string", example="No longer using the app", description="Optional reason for account deletion"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Account deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Account deleted successfully.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error - incorrect password",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password is incorrect."))
     *              )
     *          )
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return $this->sendError('Validation Error', ['password' => ['The password is incorrect.']], 422);
        }

        // Log deletion reason if provided (optional: store in a separate table for analytics)
        if ($request->reason) {
            \Log::info('Account deletion', [
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
        // Note: We anonymize instead of hard delete to preserve invoice/order history integrity
        $user->update([
            'name' => 'Deleted User',
            'email' => 'deleted_' . $user->id . '_' . time() . '@deleted.local',
            'mobile_number' => null,
            'address' => null,
            'avatar' => null,
            'date_of_birth' => null,
            'is_approved' => false,
            'password' => Hash::make(\Str::random(32)), // Invalidate password
        ]);

        return $this->sendResponse(null, 'Account deleted successfully.');
    }
}
