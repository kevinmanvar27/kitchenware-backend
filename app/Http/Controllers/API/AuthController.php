<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for Authentication"
 * )
 */
class AuthController extends ApiController
{
    /**
     * User login
     * 
     * @OA\Post(
     *      path="/api/v1/login",
     *      operationId="loginUser",
     *      tags={"Authentication"},
     *      summary="User login",
     *      description="Authenticate a user and return an access token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="Password123"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful login",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
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
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'nullable|string|max:500', // FCM device token for push notifications
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->sendError('Unauthorized', ['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        
        // Get frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // Check if user is a regular user (not admin/vendor)
        if ($user->user_role === 'user') {
            // For admin_approval_required mode, check if user is approved
            if ($accessPermission === 'admin_approval_required' && !$user->is_approved) {
                Auth::logout();
                $pendingMessage = $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.';
                return $this->sendError('Account Pending Approval', [
                    'error' => $pendingMessage,
                    'approval_status' => 'pending',
                    'requires_approval' => true
                ], 403);
            }
            
            // For registered_users_only mode, also check approval status
            if ($accessPermission === 'registered_users_only' && !$user->is_approved) {
                Auth::logout();
                return $this->sendError('Account Not Approved', [
                    'error' => 'Your account is not approved. Please contact the administrator.',
                    'approval_status' => 'pending',
                    'requires_approval' => true
                ], 403);
            }
        }
        
        $token = $user->createToken('API Token')->plainTextToken;

        // Store device token for push notifications if provided
        $deviceTokenStored = false;
        if ($request->filled('device_token')) {
            $deviceTokenStored = $user->updateDeviceToken($request->device_token);
        }

        $success['token'] = $token;
        $success['user'] = $user;
        $success['is_approved'] = $user->is_approved;
        $success['device_token_registered'] = $deviceTokenStored;
        $success['frontend_access'] = [
            'permission' => $accessPermission,
            'requires_approval' => $accessPermission === 'admin_approval_required',
        ];

        return $this->sendResponse($success, 'User login successful');
    }

    /**
     * User registration
     * 
     * @OA\Post(
     *      path="/api/v1/register",
     *      operationId="registerUser",
     *      tags={"Authentication"},
     *      summary="User registration",
     *      description="Register a new user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","password","password_confirmation"},
     *              @OA\Property(property="name", type="string", format="name", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="Password123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="Password123"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful registration",
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Get frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // Check if registration is allowed
        if ($accessPermission === 'registered_users_only') {
            return $this->sendError('Registration Disabled', [
                'error' => 'Registration is disabled. Only existing users can access the site.',
                'registration_allowed' => false
            ], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Determine if user should be auto-approved based on access permission
        // For 'open_for_all' mode, users are auto-approved
        // For 'admin_approval_required' mode, users need admin approval
        $isApproved = ($accessPermission === 'open_for_all');
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_role' => 'user',
            'is_approved' => $isApproved,
        ]);

        // Prepare response based on access permission
        $message = 'User registered successfully';
        $requiresApproval = false;
        
        if ($accessPermission === 'admin_approval_required') {
            $message = 'Registration successful. Your account is pending admin approval. Please wait for approval before logging in.';
            $requiresApproval = true;
            
            // Don't generate token for users pending approval
            $success['user'] = $user;
            $success['is_approved'] = false;
            $success['requires_approval'] = true;
            $success['pending_message'] = $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.';
            
            return $this->sendResponse($success, $message, 201);
        }
        
        // For open_for_all mode, generate token and allow immediate access
        $token = $user->createToken('API Token')->plainTextToken;

        $success['token'] = $token;
        $success['user'] = $user;
        $success['is_approved'] = $isApproved;
        $success['requires_approval'] = $requiresApproval;

        return $this->sendResponse($success, $message, 201);
    }

    /**
     * User logout
     * 
     * @OA\Post(
     *      path="/api/v1/logout",
     *      operationId="logoutUser",
     *      tags={"Authentication"},
     *      summary="User logout",
     *      description="Logout the authenticated user",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful logout",
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
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'User logged out successfully');
    }

    /**
     * Get authenticated user
     * 
     * @OA\Get(
     *      path="/api/v1/user",
     *      operationId="getAuthenticatedUser",
     *      tags={"Authentication"},
     *      summary="Get authenticated user",
     *      description="Get details of the authenticated user",
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
    public function user(Request $request)
    {
        $user = $request->user();
        
        // Get frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        $userData = $user->toArray();
        $userData['frontend_access'] = [
            'permission' => $accessPermission,
            'is_approved' => $user->is_approved,
            'requires_approval' => $accessPermission === 'admin_approval_required',
        ];
        
        return $this->sendResponse($userData, 'User retrieved successfully');
    }
    
    /**
     * Check user approval status
     * 
     * @OA\Get(
     *      path="/api/v1/check-approval-status",
     *      operationId="checkApprovalStatus",
     *      tags={"Authentication"},
     *      summary="Check user approval status",
     *      description="Check if the authenticated user's account is approved",
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
    public function checkApprovalStatus(Request $request)
    {
        $user = $request->user();
        
        // Get frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        $data = [
            'is_approved' => $user->is_approved,
            'frontend_access_permission' => $accessPermission,
            'requires_approval' => $accessPermission === 'admin_approval_required',
            'can_access' => $this->canUserAccess($user, $accessPermission),
        ];
        
        if (!$user->is_approved && $accessPermission === 'admin_approval_required') {
            $data['pending_message'] = $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.';
        }
        
        return $this->sendResponse($data, 'Approval status retrieved successfully');
    }
    
    /**
     * Check if user can access based on frontend access permission
     * 
     * @param User $user
     * @param string $accessPermission
     * @return bool
     */
    private function canUserAccess($user, $accessPermission)
    {
        // Admin users always have access
        if (in_array($user->user_role, ['super_admin', 'admin', 'editor', 'staff'])) {
            return true;
        }
        
        // For open_for_all, everyone can access
        if ($accessPermission === 'open_for_all') {
            return true;
        }
        
        // For registered_users_only and admin_approval_required, check approval status
        return $user->is_approved;
    }
}