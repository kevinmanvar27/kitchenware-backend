<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\VendorCustomer;
use App\Models\Vendor;

/**
 * @OA\Tag(
 *     name="Customer Auth",
 *     description="API Endpoints for Vendor Customer Authentication"
 * )
 */
class CustomerAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/customer/login",
     *     summary="Customer login",
     *     description="Authenticate a vendor customer and return an access token. Customer can only see products from their vendor.",
     *     operationId="customerLogin",
     *     tags={"Customer Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="customer@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123"),
     *             @OA\Property(property="vendor_slug", type="string", example="johns-store", description="Optional: The vendor store slug or ID. If not provided, will search by email.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful login"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Account is inactive"),
     *     @OA\Response(response=404, description="Customer not found")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'vendor_slug' => 'nullable|string',
            'device_token' => 'nullable|string|max:500', // FCM device token for push notifications
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $customer = null;
        $vendor = null;

        // If vendor_slug is provided, find customer for that specific vendor
        if ($request->filled('vendor_slug')) {
            $vendor = Vendor::where('store_slug', $request->vendor_slug)
                ->orWhere('id', $request->vendor_slug)
                ->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found',
                    'data' => null
                ], 404);
            }

            if (!$vendor->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This vendor store is not available',
                    'data' => null
                ], 403);
            }

            $customer = VendorCustomer::where('vendor_id', $vendor->id)
                ->where('email', $request->email)
                ->first();
        } else {
            // No vendor_slug provided - find customer by email only
            $customer = VendorCustomer::where('email', $request->email)
                ->whereNotNull('password')
                ->first();
            
            if ($customer) {
                $vendor = $customer->vendor;
            }
        }

        // Validate customer and password
        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null
            ], 401);
        }

        // Check if vendor is approved
        if (!$vendor || !$vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'This vendor store is not available',
                'data' => null
            ], 403);
        }

        if (!$customer->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact the vendor.',
                'data' => null
            ], 403);
        }

        // Update last login
        $customer->updateLastLogin();

        // Store device token for push notifications if provided
        $deviceTokenStored = false;
        if ($request->filled('device_token')) {
            $deviceTokenStored = $customer->updateDeviceToken($request->device_token);
        }

        // Create token
        $token = $customer->createToken('customer-token', ['customer'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'mobile_number' => $customer->mobile_number,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'discount_percentage' => $customer->discount_percentage,
                    'profile_avatar_url' => $customer->profile_avatar_url,
                    'last_login_at' => $customer->last_login_at,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'banner_redirect_url' => $vendor->banner_redirect_url,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'device_token_registered' => $deviceTokenStored,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer/logout",
     *     summary="Customer logout",
     *     description="Logout the authenticated customer",
     *     operationId="customerLogout",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful logout"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer/register-device",
     *     summary="Register device token for push notifications",
     *     description="Register or update the FCM device token for the authenticated customer",
     *     operationId="customerRegisterDeviceToken",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"device_token"},
     *             @OA\Property(property="device_token", type="string", description="FCM device token")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Device token registered successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function registerDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $customer = $request->user();
        $success = $customer->updateDeviceToken($request->device_token);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Device token registered successfully',
                'data' => [
                    'device_token_registered' => true,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to register device token',
            'data' => null
        ], 500);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/profile",
     *     summary="Get customer profile",
     *     description="Get the authenticated customer's profile",
     *     operationId="getCustomerProfile",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function profile(Request $request)
    {
        $customer = $request->user();
        $vendor = $customer->vendor;

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'mobile_number' => $customer->mobile_number,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'discount_percentage' => $customer->discount_percentage,
                    'profile_avatar_url' => $customer->profile_avatar_url,
                    'created_at' => $customer->created_at,
                    'last_login_at' => $customer->last_login_at,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'banner_redirect_url' => $vendor->banner_redirect_url,
                    'business_phone' => $vendor->business_phone,
                    'business_email' => $vendor->business_email,
                ]
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/customer/profile",
     *     summary="Update customer profile",
     *     description="Update the authenticated customer's profile",
     *     operationId="updateCustomerProfile",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="mobile_number", type="string", example="9876543210"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="postal_code", type="string", example="400001")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateProfile(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'mobile_number' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $customer->update($request->only([
            'name',
            'mobile_number',
            'address',
            'city',
            'state',
            'postal_code',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'mobile_number' => $customer->mobile_number,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'profile_avatar_url' => $customer->profile_avatar_url,
                ]
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/customer/change-password",
     *     summary="Change customer password",
     *     description="Change the authenticated customer's password",
     *     operationId="changeCustomerPassword",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password","password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="OldPassword123"),
     *             @OA\Property(property="password", type="string", format="password", example="NewPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password changed successfully"),
     *     @OA\Response(response=401, description="Unauthenticated or current password incorrect"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function changePassword(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null
            ], 401);
        }

        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            'data' => null
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer/avatar",
     *     summary="Upload customer profile avatar",
     *     description="Upload or update the customer's profile avatar image",
     *     operationId="customerUploadAvatar",
     *     tags={"Customer Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"avatar"},
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary",
     *                     description="Avatar image file (jpeg, png, jpg, gif - max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avatar uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile avatar uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="profile_avatar_url", type="string", example="http://example.com/storage/customer-avatars/1/avatar_1707292800.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error - Invalid file type or size")
     * )
     */
    public function uploadAvatar(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Delete old avatar if exists
        if ($customer->profile_avatar) {
            Storage::disk('public')->delete('customer-avatars/' . $customer->vendor_id . '/' . $customer->profile_avatar);
        }

        // Store new avatar
        $file = $request->file('avatar');
        $filename = 'avatar_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('customer-avatars/' . $customer->vendor_id, $filename, 'public');

        // Update customer record
        $customer->update(['profile_avatar' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Profile avatar uploaded successfully',
            'data' => [
                'profile_avatar_url' => $customer->profile_avatar_url
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer/check-vendors",
     *     summary="Check which vendor stores a customer belongs to",
     *     description="Returns list of vendor stores associated with customer credentials. Used before login to show vendor selection dropdown.",
     *     operationId="customerCheckVendors",
     *     tags={"Customer Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="customer@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Vendor stores found"),
     *     @OA\Response(response=404, description="No vendor stores found for these credentials")
     * )
     */
    public function checkVendors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Find all vendor customers with this email
        $vendorCustomers = VendorCustomer::where('email', $request->email)
            ->whereNotNull('password')
            ->where('is_active', true)
            ->with('vendor')
            ->get();

        if ($vendorCustomers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with these credentials',
                'data' => null
            ], 404);
        }

        // Verify password and filter valid vendors
        $validVendorStores = [];
        foreach ($vendorCustomers as $customer) {
            if (Hash::check($request->password, $customer->password)) {
                $vendor = $customer->vendor;
                
                // Only include approved vendors
                if ($vendor && $vendor->isApproved()) {
                    $validVendorStores[] = [
                        'vendor_customer_id' => $customer->id,
                        'vendor_id' => $vendor->id,
                        'store_name' => $vendor->store_name,
                        'store_slug' => $vendor->store_slug,
                        'store_logo_url' => $vendor->store_logo_url,
                        'discount_percentage' => $customer->discount_percentage,
                    ];
                }
            }
        }

        if (empty($validVendorStores)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or no active vendor stores found',
                'data' => null
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendor stores retrieved successfully',
            'data' => [
                'vendor_stores' => $validVendorStores,
                'total_stores' => count($validVendorStores),
                'auto_login' => count($validVendorStores) === 1, // Flag for auto-login
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer/switch-vendor",
     *     summary="Switch to a different vendor store",
     *     description="Switch the authenticated customer to a different vendor store they belong to",
     *     operationId="customerSwitchVendor",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vendor_slug"},
     *             @OA\Property(property="vendor_slug", type="string", example="johns-store", description="The vendor store slug to switch to"),
     *             @OA\Property(property="device_token", type="string", description="Optional FCM device token for push notifications")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successfully switched vendor store"),
     *     @OA\Response(response=403, description="Customer doesn't belong to this vendor"),
     *     @OA\Response(response=404, description="Vendor not found")
     * )
     */
    public function switchVendor(Request $request)
    {
        $currentCustomer = $request->user();

        $validator = Validator::make($request->all(), [
            'vendor_slug' => 'required|string',
            'device_token' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Find the target vendor
        $vendor = Vendor::where('store_slug', $request->vendor_slug)
            ->orWhere('id', $request->vendor_slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor store not found',
                'data' => null
            ], 404);
        }

        if (!$vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'This vendor store is not available',
                'data' => null
            ], 403);
        }

        // Check if customer belongs to this vendor
        $newCustomerAccount = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('email', $currentCustomer->email)
            ->where('is_active', true)
            ->first();

        if (!$newCustomerAccount) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this vendor store',
                'data' => null
            ], 403);
        }

        // Customer is already authenticated and both accounts share the same email
        // This is sufficient verification to allow the switch

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Update last login for new vendor account
        $newCustomerAccount->updateLastLogin();

        // Store device token if provided
        if ($request->filled('device_token')) {
            $newCustomerAccount->updateDeviceToken($request->device_token);
        }

        // Create new token for the new vendor customer account
        $token = $newCustomerAccount->createToken('customer-token', ['customer'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Successfully switched to ' . $vendor->store_name,
            'data' => [
                'customer' => [
                    'id' => $newCustomerAccount->id,
                    'name' => $newCustomerAccount->name,
                    'email' => $newCustomerAccount->email,
                    'mobile_number' => $newCustomerAccount->mobile_number,
                    'address' => $newCustomerAccount->address,
                    'city' => $newCustomerAccount->city,
                    'state' => $newCustomerAccount->state,
                    'postal_code' => $newCustomerAccount->postal_code,
                    'discount_percentage' => $newCustomerAccount->discount_percentage,
                    'profile_avatar_url' => $newCustomerAccount->profile_avatar_url,
                    'last_login_at' => $newCustomerAccount->last_login_at,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'banner_redirect_url' => $vendor->banner_redirect_url,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/available-vendors",
     *     summary="Get all vendor stores available to the authenticated customer",
     *     description="Returns list of all vendor stores the customer can switch to",
     *     operationId="customerAvailableVendors",
     *     tags={"Customer Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Available vendor stores retrieved"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function availableVendors(Request $request)
    {
        $customer = $request->user();
        
        // Get all vendor accounts for this customer's email
        $vendorCustomers = VendorCustomer::where('email', $customer->email)
            ->where('is_active', true)
            ->with('vendor')
            ->get();

        $availableStores = [];
        foreach ($vendorCustomers as $vendorCustomer) {
            $vendor = $vendorCustomer->vendor;
            
            if ($vendor && $vendor->isApproved()) {
                $availableStores[] = [
                    'vendor_customer_id' => $vendorCustomer->id,
                    'vendor_id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'banner_redirect_url' => $vendor->banner_redirect_url,
                    'discount_percentage' => $vendorCustomer->discount_percentage,
                    'is_current' => $vendorCustomer->id === $customer->id,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Available vendor stores retrieved successfully',
            'data' => [
                'current_vendor' => [
                    'id' => $customer->vendor->id,
                    'store_name' => $customer->vendor->store_name,
                    'store_slug' => $customer->vendor->store_slug,
                    'store_logo_url' => $customer->vendor->store_logo_url,
                ],
                'available_stores' => $availableStores,
                'total_stores' => count($availableStores),
            ]
        ]);
    }
}
