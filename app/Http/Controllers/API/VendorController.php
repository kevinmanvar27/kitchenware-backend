<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ProformaInvoice;
use App\Models\Lead;
use App\Models\Coupon;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\VendorStaff;
use App\Mail\VendorRegistrationPending;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Vendor",
 *     description="API Endpoints for Vendor Management"
 * )
 */
class VendorController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/vendor/register",
     *     summary="Register as a vendor",
     *     description="Register a new vendor account",
     *     operationId="vendorRegister",
     *     tags={"Vendor"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","store_name","business_phone"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="vendor@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Password123"),
     *             @OA\Property(property="store_name", type="string", example="John's Electronics Store"),
     *             @OA\Property(property="store_description", type="string", example="Best electronics store in town"),
     *             @OA\Property(property="business_email", type="string", format="email", example="business@example.com"),
     *             @OA\Property(property="business_phone", type="string", example="9876543210"),
     *             @OA\Property(property="business_address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="country", type="string", example="India"),
     *             @OA\Property(property="postal_code", type="string", example="400001"),
     *             @OA\Property(property="gst_number", type="string", example="27AABCU9603R1ZM"),
     *             @OA\Property(property="pan_number", type="string", example="ABCDE1234F")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Vendor registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'required|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_vendor' => true,
        ]);

        // Create vendor profile
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => $request->store_name,
            'store_description' => $request->store_description,
            'business_email' => $request->business_email ?? $request->email,
            'business_phone' => $request->business_phone,
            'business_address' => $request->business_address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'status' => Vendor::STATUS_PENDING,
            'commission_rate' => 10.00, // Default commission rate
        ]);

        // Send registration pending email
        try {
            Mail::to($user->email)->send(new VendorRegistrationPending($user, $vendor));
        } catch (\Exception $e) {
            Log::error('Failed to send vendor registration email: ' . $e->getMessage());
        }

        $token = $user->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Vendor registered successfully. Your account is pending approval.',
            'data' => [
                'user' => $user,
                'vendor' => $vendor,
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/login",
     *     summary="Vendor login",
     *     description="Authenticate a vendor and return an access token",
     *     operationId="vendorLogin",
     *     tags={"Vendor"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="vendor@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful login"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Vendor account not approved")
     * )
     */
    public function login(Request $request)
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

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->vendor) {
            return response()->json([
                'success' => false,
                'message' => 'This account is not registered as a vendor'
            ], 403);
        }

        $vendor = $user->vendor;
        $statusMessage = null;

        if ($vendor->status === Vendor::STATUS_PENDING) {
            $statusMessage = 'Your vendor account is pending approval.';
        } elseif ($vendor->status === Vendor::STATUS_REJECTED) {
            $statusMessage = 'Your vendor account has been rejected. Reason: ' . ($vendor->rejection_reason ?? 'Not specified');
        } elseif ($vendor->status === Vendor::STATUS_SUSPENDED) {
            $statusMessage = 'Your vendor account has been suspended.';
        }

        $token = $user->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'vendor' => $vendor,
                'token' => $token,
                'vendor_status' => $vendor->status,
                'status_message' => $statusMessage,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/dashboard",
     *     summary="Get vendor dashboard data",
     *     description="Returns vendor dashboard statistics and analytics",
     *     operationId="vendorDashboard",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor")
     * )
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 403);
        }

        // Basic counts
        $productCount = Product::where('vendor_id', $vendor->id)->count();
        $categoryCount = Category::where('vendor_id', $vendor->id)->count();
        $activeProductCount = Product::where('vendor_id', $vendor->id)->where('status', 'published')->count();

        // Get vendor orders
        $vendorOrders = $this->getVendorOrders($vendor->id);

        // Revenue statistics
        $totalRevenue = $vendorOrders['delivered']->sum('vendor_total');
        $monthlyRevenue = $vendorOrders['delivered']
            ->filter(fn($order) => Carbon::parse($order['created_at'])->isCurrentMonth())
            ->sum('vendor_total');

        $lastMonthRevenue = $vendorOrders['delivered']
            ->filter(function($order) {
                $date = Carbon::parse($order['created_at']);
                return $date->month === Carbon::now()->subMonth()->month 
                    && $date->year === Carbon::now()->subMonth()->year;
            })
            ->sum('vendor_total');

        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
            : ($monthlyRevenue > 0 ? 100 : 0);

        // Order statistics
        $totalOrders = $vendorOrders['all']->count();
        $pendingOrders = $vendorOrders['pending']->count();
        $deliveredOrders = $vendorOrders['delivered']->count();

        // Today's statistics
        $todayOrders = $vendorOrders['all']
            ->filter(fn($order) => Carbon::parse($order['created_at'])->isToday())
            ->count();
        $todayRevenue = $vendorOrders['delivered']
            ->filter(fn($order) => Carbon::parse($order['created_at'])->isToday())
            ->sum('vendor_total');

        // Low stock products count
        $lowStockCount = Product::where('vendor_id', $vendor->id)
            ->where('in_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        // Out of stock count
        $outOfStockCount = Product::where('vendor_id', $vendor->id)
            ->where(function($query) {
                $query->where('in_stock', false)
                      ->orWhere('stock_quantity', '<=', 0);
            })->count();

        // Commission info
        $commissionRate = $vendor->commission_rate;
        $totalCommission = $totalRevenue * ($commissionRate / 100);
        $netEarnings = $totalRevenue - $totalCommission;

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'status' => $vendor->status,
                    'store_logo_url' => $vendor->store_logo_url,
                ],
                'statistics' => [
                    'products' => [
                        'total' => $productCount,
                        'active' => $activeProductCount,
                        'low_stock' => $lowStockCount,
                        'out_of_stock' => $outOfStockCount,
                    ],
                    'categories' => $categoryCount,
                    'orders' => [
                        'total' => $totalOrders,
                        'pending' => $pendingOrders,
                        'delivered' => $deliveredOrders,
                        'today' => $todayOrders,
                    ],
                    'revenue' => [
                        'total' => round($totalRevenue, 2),
                        'monthly' => round($monthlyRevenue, 2),
                        'today' => round($todayRevenue, 2),
                        'growth_percentage' => $revenueGrowth,
                    ],
                    'commission' => [
                        'rate' => $commissionRate,
                        'total_commission' => round($totalCommission, 2),
                        'net_earnings' => round($netEarnings, 2),
                    ],
                ],
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/profile",
     *     summary="Get vendor profile",
     *     description="Returns the authenticated vendor's profile data",
     *     operationId="getVendorProfile",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendor profile retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url,
                ],
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_description' => $vendor->store_description,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'banner_redirect_url' => $vendor->banner_redirect_url,
                    'business_email' => $vendor->business_email,
                    'business_phone' => $vendor->business_phone,
                    'business_address' => $vendor->business_address,
                    'city' => $vendor->city,
                    'state' => $vendor->state,
                    'country' => $vendor->country,
                    'postal_code' => $vendor->postal_code,
                    'gst_number' => $vendor->gst_number,
                    'pan_number' => $vendor->pan_number,
                    'bank_name' => $vendor->bank_name,
                    'bank_account_number' => $vendor->bank_account_number ? '****' . substr($vendor->bank_account_number, -4) : null,
                    'bank_ifsc_code' => $vendor->bank_ifsc_code,
                    'bank_account_holder_name' => $vendor->bank_account_holder_name,
                    'commission_rate' => $vendor->commission_rate,
                    'status' => $vendor->status,
                    'is_featured' => $vendor->is_featured,
                    'social_links' => $vendor->social_links,
                    'approved_at' => $vendor->approved_at,
                    'created_at' => $vendor->created_at,
                ],
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/profile",
     *     summary="Update vendor profile",
     *     description="Update the authenticated vendor's profile data",
     *     operationId="updateVendorProfile",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="store_name", type="string", example="John's Store"),
     *             @OA\Property(property="store_description", type="string", example="Best store in town"),
     *             @OA\Property(property="business_email", type="string", format="email", example="business@example.com"),
     *             @OA\Property(property="business_phone", type="string", example="9876543210"),
     *             @OA\Property(property="business_address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="country", type="string", example="India"),
     *             @OA\Property(property="postal_code", type="string", example="400001"),
     *             @OA\Property(property="gst_number", type="string", example="27AABCU9603R1ZM"),
     *             @OA\Property(property="pan_number", type="string", example="ABCDE1234F")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'store_name' => 'sometimes|string|max:255',
            'store_description' => 'nullable|string',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'sometimes|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Update user name if provided
        if ($request->has('name')) {
            $user->update(['name' => $request->name]);
        }

        // Update vendor profile
        $vendor->update($request->only([
            'store_name', 'store_description', 'business_email', 'business_phone',
            'business_address', 'city', 'state', 'country', 'postal_code',
            'gst_number', 'pan_number'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user->fresh(),
                'vendor' => $vendor->fresh(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/store-logo",
     *     summary="Upload store logo",
     *     description="Upload or update the vendor's store logo",
     *     operationId="uploadStoreLogo",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="logo", type="string", format="binary", description="Store logo image file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Logo uploaded successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function uploadStoreLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        // Delete old logo if exists
        if ($vendor->store_logo) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_logo);
            Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_logo);
        }

        // Store new logo
        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('vendor/' . $vendor->id, $filename, 'public');

        $vendor->update(['store_logo' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Store logo uploaded successfully',
            'data' => [
                'store_logo_url' => $vendor->fresh()->store_logo_url,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/store-banner",
     *     summary="Upload store banner",
     *     description="Upload or update the vendor's store banner (file upload or URL)",
     *     operationId="uploadStoreBanner",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="banner", type="string", format="binary", description="Store banner image file"),
     *                 @OA\Property(property="banner_image_url", type="string", format="url", description="External banner image URL"),
     *                 @OA\Property(property="banner_redirect_url", type="string", format="url", description="URL to redirect when banner is clicked")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Banner uploaded successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function uploadStoreBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'banner_image_url' => 'nullable|url|max:500',
            'banner_redirect_url' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Check that at least one of banner or banner_image_url is provided
        if (!$request->hasFile('banner') && !$request->filled('banner_image_url')) {
            return response()->json([
                'success' => false,
                'message' => 'Either banner file or banner_image_url must be provided'
            ], 422);
        }

        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        // Handle banner image URL (external URL)
        if ($request->filled('banner_image_url')) {
            // If banner_image_url is provided, clear the uploaded banner file
            if ($vendor->store_banner) {
                Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
                Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_banner);
            }
            $vendor->update([
                'banner_image_url' => $request->banner_image_url,
                'store_banner' => null,
            ]);
        }
        // Handle banner upload (file)
        elseif ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($vendor->store_banner) {
                Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
                Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_banner);
            }

            // Store new banner
            $file = $request->file('banner');
            $filename = 'banner_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('vendor/' . $vendor->id, $filename, 'public');

            $vendor->update([
                'store_banner' => $filename,
                'banner_image_url' => null,
            ]);
        }

        // Update banner redirect URL if provided
        if ($request->filled('banner_redirect_url')) {
            $vendor->update([
                'banner_redirect_url' => $request->banner_redirect_url,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store banner uploaded successfully',
            'data' => [
                'store_banner_url' => $vendor->fresh()->store_banner_url,
                'banner_redirect_url' => $vendor->fresh()->banner_redirect_url,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/store-banner",
     *     summary="Get store banner",
     *     description="Get the vendor's store banner URL",
     *     operationId="getStoreBanner",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Banner retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Store banner retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="store_banner_url", type="string", example="https://example.com/banner.jpg"),
     *                 @OA\Property(property="banner_image_url", type="string", example="https://example.com/banner.jpg"),
     *                 @OA\Property(property="banner_redirect_url", type="string", example="https://example.com/products/20"),
     *                 @OA\Property(property="store_banner", type="string", example="banner_1234567890.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Vendor profile not found")
     * )
     */
    public function getStoreBanner(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store banner retrieved successfully',
            'data' => [
                'store_banner_url' => $vendor->store_banner_url,
                'banner_image_url' => $vendor->banner_image_url,
                'banner_redirect_url' => $vendor->banner_redirect_url,
                'store_banner' => $vendor->store_banner,
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/bank-details",
     *     summary="Update bank details",
     *     description="Update the vendor's bank account details",
     *     operationId="updateBankDetails",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bank_name","bank_account_number","bank_ifsc_code","bank_account_holder_name"},
     *             @OA\Property(property="bank_name", type="string", example="State Bank of India"),
     *             @OA\Property(property="bank_account_number", type="string", example="1234567890123456"),
     *             @OA\Property(property="bank_ifsc_code", type="string", example="SBIN0001234"),
     *             @OA\Property(property="bank_account_holder_name", type="string", example="John Doe")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Bank details updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateBankDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_ifsc_code' => 'required|string|max:20',
            'bank_account_holder_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $vendor->update($request->only([
            'bank_name', 'bank_account_number', 'bank_ifsc_code', 'bank_account_holder_name'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Bank details updated successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/products",
     *     summary="Get vendor products",
     *     description="Returns list of vendor's products with pagination",
     *     operationId="getVendorProducts",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status (published, draft)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", description="Search by product name", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", description="Filter by category", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function products(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $query = Product::where('vendor_id', $vendor->id)
            ->with(['variations']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->whereJsonContains('product_categories', [['category_id' => (int)$request->category_id]]);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/products",
     *     summary="Create a new product",
     *     description="Create a new product for the vendor",
     *     operationId="createVendorProduct",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","mrp","status"},
     *             @OA\Property(property="name", type="string", example="New Product"),
     *             @OA\Property(property="description", type="string", example="Product description"),
     *             @OA\Property(property="mrp", type="number", format="float", example=999.99),
     *             @OA\Property(property="selling_price", type="number", format="float", example=899.99),
     *             @OA\Property(property="in_stock", type="boolean", example=true),
     *             @OA\Property(property="stock_quantity", type="integer", example=100),
     *             @OA\Property(property="low_quantity_threshold", type="integer", example=10),
     *             @OA\Property(property="status", type="string", example="published"),
     *             @OA\Property(property="product_type", type="string", example="simple"),
     *             @OA\Property(property="main_photo_id", type="integer", example=1),
     *             @OA\Property(property="product_gallery", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="product_categories", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Product created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createProduct(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        if (!$vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Your vendor account must be approved to create products'
            ], 403);
        }

        $productType = $request->product_type ?? 'simple';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mrp' => $productType === 'simple' ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'in_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:published,draft',
            'product_type' => 'nullable|in:simple,variable',
            'main_photo_id' => 'nullable|integer|exists:media,id',
            'product_gallery' => 'nullable|array',
            'product_categories' => 'nullable|array',
            'product_attributes' => 'nullable|array',
            'variations' => $productType === 'variable' ? 'required|array|min:1' : 'nullable|array',
            'variations.*.sku' => 'nullable|string',
            'variations.*.mrp' => 'required_with:variations|numeric|min:0',
            'variations.*.selling_price' => 'nullable|numeric|min:0',
            'variations.*.stock_quantity' => 'required_with:variations|integer|min:0',
            'variations.*.low_quantity_threshold' => 'nullable|integer|min:0',
            'variations.*.attribute_values' => 'required_with:variations|array',
            'variations.*.image_id' => 'nullable|integer|exists:media,id',
            'variations.*.is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        \DB::beginTransaction();
        
        try {
            // Prepare product data
            $productData = [
                'vendor_id' => $vendor->id,
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'product_type' => $productType,
                'main_photo_id' => $request->main_photo_id,
                'product_gallery' => $request->product_gallery ?? [],
                'product_categories' => $request->product_categories ?? [],
                'product_attributes' => $request->product_attributes ?? [],
                'low_quantity_threshold' => $request->low_quantity_threshold ?? 10,
            ];

            // Handle simple vs variable product
            if ($productType === 'simple') {
                $productData['mrp'] = $request->mrp;
                $productData['selling_price'] = $request->selling_price ?? $request->mrp;
                $productData['in_stock'] = $request->in_stock ?? true;
                $productData['stock_quantity'] = $request->stock_quantity ?? 0;
            } else {
                // Variable product - set defaults
                $productData['mrp'] = $request->mrp ?? 0;
                $productData['selling_price'] = $request->selling_price;
                $productData['in_stock'] = true;
                $productData['stock_quantity'] = 0;
            }

            $product = Product::create($productData);

            // Handle variations for variable products
            if ($productType === 'variable' && $request->has('variations')) {
                $variations = $request->variations;
                
                foreach ($variations as $index => $variationData) {
                    $stockQty = $variationData['stock_quantity'] ?? 0;
                    
                    $product->variations()->create([
                        'sku' => $variationData['sku'] ?? null,
                        'mrp' => $variationData['mrp'] ?? 0,
                        'selling_price' => $variationData['selling_price'] ?? null,
                        'stock_quantity' => $stockQty,
                        'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                        'in_stock' => $stockQty > 0,
                        'attribute_values' => $variationData['attribute_values'] ?? [],
                        'image_id' => $variationData['image_id'] ?? null,
                        'is_default' => $variationData['is_default'] ?? ($index === 0),
                    ]);
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['variations'])
            ], 201);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error creating product via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/products/{id}",
     *     summary="Update a product",
     *     description="Update an existing product including variations",
     *     operationId="updateVendorProduct",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Product"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="mrp", type="number", format="float", example=999.99),
     *             @OA\Property(property="selling_price", type="number", format="float", example=899.99),
     *             @OA\Property(property="in_stock", type="boolean", example=true),
     *             @OA\Property(property="stock_quantity", type="integer", example=100),
     *             @OA\Property(property="status", type="string", example="published"),
     *             @OA\Property(property="product_type", type="string", example="variable"),
     *             @OA\Property(property="variations", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function updateProduct(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $productType = $request->product_type ?? $product->product_type ?? 'simple';

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'mrp' => 'sometimes|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'in_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'sometimes|in:published,draft',
            'product_type' => 'nullable|in:simple,variable',
            'main_photo_id' => 'nullable|integer|exists:media,id',
            'product_gallery' => 'nullable|array',
            'product_categories' => 'nullable|array',
            'product_attributes' => 'nullable|array',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|integer',
            'variations.*.sku' => 'nullable|string',
            'variations.*.mrp' => 'nullable|numeric|min:0',
            'variations.*.selling_price' => 'nullable|numeric|min:0',
            'variations.*.stock_quantity' => 'nullable|integer|min:0',
            'variations.*.low_quantity_threshold' => 'nullable|integer|min:0',
            'variations.*.attribute_values' => 'nullable|array',
            'variations.*.image_id' => 'nullable|integer|exists:media,id',
            'variations.*.is_default' => 'nullable|boolean',
            'variations.*._delete' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        \DB::beginTransaction();
        
        try {
            // Prepare product data
            $productData = $request->only([
                'name', 'description', 'status', 'main_photo_id',
                'meta_title', 'meta_description', 'meta_keywords'
            ]);
            
            $productData['product_type'] = $productType;
            
            if ($request->has('product_gallery')) {
                $productData['product_gallery'] = $request->product_gallery ?? [];
            }
            if ($request->has('product_categories')) {
                $productData['product_categories'] = $request->product_categories ?? [];
            }
            if ($request->has('product_attributes')) {
                $productData['product_attributes'] = $request->product_attributes ?? [];
            }
            if ($request->has('low_quantity_threshold')) {
                $productData['low_quantity_threshold'] = $request->low_quantity_threshold ?? 10;
            }

            // Handle simple vs variable product
            if ($productType === 'simple') {
                if ($request->has('mrp')) {
                    $productData['mrp'] = $request->mrp;
                }
                if ($request->has('selling_price')) {
                    $productData['selling_price'] = $request->selling_price;
                }
                if ($request->has('in_stock')) {
                    $productData['in_stock'] = $request->in_stock;
                }
                if ($request->has('stock_quantity')) {
                    $productData['stock_quantity'] = $request->stock_quantity;
                }
            } else {
                // Variable product
                $productData['in_stock'] = true;
                $productData['stock_quantity'] = 0;
            }

            $product->update($productData);

            // Handle variations for variable products
            if ($productType === 'variable' && $request->has('variations')) {
                $variations = $request->variations;
                $existingVariationIds = $product->variations()->pluck('id')->toArray();
                $updatedVariationIds = [];
                
                foreach ($variations as $index => $variationData) {
                    // Skip variations marked for deletion
                    if (isset($variationData['_delete']) && $variationData['_delete']) {
                        if (isset($variationData['id']) && $variationData['id']) {
                            // Will be deleted below
                            continue;
                        }
                        continue;
                    }
                    
                    $stockQty = $variationData['stock_quantity'] ?? 0;
                    
                    $variationPayload = [
                        'sku' => $variationData['sku'] ?? null,
                        'mrp' => $variationData['mrp'] ?? 0,
                        'selling_price' => $variationData['selling_price'] ?? null,
                        'stock_quantity' => $stockQty,
                        'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                        'in_stock' => $stockQty > 0,
                        'attribute_values' => $variationData['attribute_values'] ?? [],
                        'image_id' => $variationData['image_id'] ?? null,
                        'is_default' => $variationData['is_default'] ?? ($index === 0),
                    ];
                    
                    if (isset($variationData['id']) && $variationData['id']) {
                        // Update existing variation
                        $variation = $product->variations()->find($variationData['id']);
                        if ($variation) {
                            $variation->update($variationPayload);
                            $updatedVariationIds[] = $variation->id;
                        }
                    } else {
                        // Create new variation
                        $newVariation = $product->variations()->create($variationPayload);
                        $updatedVariationIds[] = $newVariation->id;
                    }
                }
                
                // Delete removed variations
                $variationsToDelete = array_diff($existingVariationIds, $updatedVariationIds);
                if (!empty($variationsToDelete)) {
                    $product->variations()->whereIn('id', $variationsToDelete)->delete();
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->fresh()->load(['variations'])
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error updating product via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendor/products/{id}",
     *     summary="Delete a product",
     *     description="Delete a product",
     *     operationId="deleteVendorProduct",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Product deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function deleteProduct(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/products/{productId}/variations",
     *     summary="Get product variations",
     *     description="Returns all variations for a specific product",
     *     operationId="getProductVariations",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="productId", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function getProductVariations(Request $request, $productId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $variations = $product->variations()->with('image')->get();

        return response()->json([
            'success' => true,
            'message' => 'Product variations retrieved successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_type' => $product->product_type,
                'variations' => $variations
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/products/{productId}/variations",
     *     summary="Add product variation",
     *     description="Add a new variation to a product",
     *     operationId="addProductVariation",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="productId", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mrp","stock_quantity","attribute_values"},
     *             @OA\Property(property="sku", type="string", example="SKU-001"),
     *             @OA\Property(property="mrp", type="number", format="float", example=999.99),
     *             @OA\Property(property="selling_price", type="number", format="float", example=899.99),
     *             @OA\Property(property="stock_quantity", type="integer", example=100),
     *             @OA\Property(property="low_quantity_threshold", type="integer", example=10),
     *             @OA\Property(property="attribute_values", type="object", example={"1": "2", "3": "4"}),
     *             @OA\Property(property="image_id", type="integer", example=1),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Variation added successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addProductVariation(Request $request, $productId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if ($product->product_type !== 'variable') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add variations to a simple product. Change product type to variable first.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'sku' => 'nullable|string|max:100',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'attribute_values' => 'required|array',
            'image_id' => 'nullable|integer|exists:media,id',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $stockQty = $request->stock_quantity ?? 0;
        
        // If this is set as default, unset other defaults
        if ($request->is_default) {
            $product->variations()->update(['is_default' => false]);
        }

        $variation = $product->variations()->create([
            'sku' => $request->sku,
            'mrp' => $request->mrp,
            'selling_price' => $request->selling_price,
            'stock_quantity' => $stockQty,
            'low_quantity_threshold' => $request->low_quantity_threshold ?? 10,
            'in_stock' => $stockQty > 0,
            'attribute_values' => $request->attribute_values,
            'image_id' => $request->image_id,
            'is_default' => $request->is_default ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Variation added successfully',
            'data' => $variation
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/products/{productId}/variations/{variationId}",
     *     summary="Update product variation",
     *     description="Update an existing product variation",
     *     operationId="updateProductVariation",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="productId", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="variationId", in="path", description="Variation ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sku", type="string", example="SKU-001"),
     *             @OA\Property(property="mrp", type="number", format="float", example=999.99),
     *             @OA\Property(property="selling_price", type="number", format="float", example=899.99),
     *             @OA\Property(property="stock_quantity", type="integer", example=100),
     *             @OA\Property(property="low_quantity_threshold", type="integer", example=10),
     *             @OA\Property(property="attribute_values", type="object"),
     *             @OA\Property(property="image_id", type="integer", example=1),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Variation updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product or variation not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateProductVariation(Request $request, $productId, $variationId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $variation = $product->variations()->find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'sku' => 'nullable|string|max:100',
            'mrp' => 'sometimes|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'attribute_values' => 'sometimes|array',
            'image_id' => 'nullable|integer|exists:media,id',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'sku', 'mrp', 'selling_price', 'stock_quantity', 
            'low_quantity_threshold', 'attribute_values', 'image_id', 'is_default'
        ]);

        // Update in_stock based on stock_quantity
        if (isset($updateData['stock_quantity'])) {
            $updateData['in_stock'] = $updateData['stock_quantity'] > 0;
        }

        // If this is set as default, unset other defaults
        if (isset($updateData['is_default']) && $updateData['is_default']) {
            $product->variations()->where('id', '!=', $variationId)->update(['is_default' => false]);
        }

        $variation->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Variation updated successfully',
            'data' => $variation->fresh()
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendor/products/{productId}/variations/{variationId}",
     *     summary="Delete product variation",
     *     description="Delete a product variation",
     *     operationId="deleteProductVariation",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="productId", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="variationId", in="path", description="Variation ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Variation deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product or variation not found")
     * )
     */
    public function deleteProductVariation(Request $request, $productId, $variationId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $variation = $product->variations()->find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found'
            ], 404);
        }

        // Check if this is the last variation
        if ($product->variations()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the last variation. A variable product must have at least one variation.'
            ], 422);
        }

        // If deleting default variation, make another one default
        if ($variation->is_default) {
            $nextVariation = $product->variations()->where('id', '!=', $variationId)->first();
            if ($nextVariation) {
                $nextVariation->update(['is_default' => true]);
            }
        }

        $variation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variation deleted successfully'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/products/{productId}/variations/{variationId}/stock",
     *     summary="Update variation stock",
     *     description="Quick update for variation stock quantity",
     *     operationId="updateVariationStock",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="productId", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="variationId", in="path", description="Variation ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"stock_quantity"},
     *             @OA\Property(property="stock_quantity", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Stock updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product or variation not found")
     * )
     */
    public function updateVariationStock(Request $request, $productId, $variationId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $variation = $product->variations()->find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $stockQty = $request->stock_quantity;
        $variation->update([
            'stock_quantity' => $stockQty,
            'in_stock' => $stockQty > 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => [
                'variation_id' => $variation->id,
                'stock_quantity' => $variation->stock_quantity,
                'in_stock' => $variation->in_stock
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/products/{productId}/variations/{variationId}/set-default",
     *     summary="Set default variation",
     *     description="Set a variation as the default for a product",
     *     operationId="setDefaultVariation",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="productId", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="variationId", in="path", description="Variation ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Default variation set successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product or variation not found")
     * )
     */
    public function setDefaultVariation(Request $request, $productId, $variationId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $product = Product::where('vendor_id', $vendor->id)->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $variation = $product->variations()->find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found'
            ], 404);
        }

        // Remove default from all other variations
        $product->variations()->update(['is_default' => false]);

        // Set this variation as default
        $variation->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default variation set successfully',
            'data' => [
                'variation_id' => $variation->id,
                'is_default' => true
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/products/variations/bulk-stock-update",
     *     summary="Bulk update variation stock",
     *     description="Update stock for multiple variations at once",
     *     operationId="bulkUpdateVariationStock",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"variations"},
     *             @OA\Property(property="variations", type="array", @OA\Items(
     *                 @OA\Property(property="variation_id", type="integer"),
     *                 @OA\Property(property="stock_quantity", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Stock updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function bulkUpdateVariationStock(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'variations' => 'required|array|min:1',
            'variations.*.variation_id' => 'required|integer|exists:product_variations,id',
            'variations.*.stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $updated = [];
        $failed = [];

        foreach ($request->variations as $variationData) {
            $variation = ProductVariation::find($variationData['variation_id']);
            
            // Verify the variation belongs to a product owned by this vendor
            if ($variation && $variation->product && $variation->product->vendor_id === $vendor->id) {
                $stockQty = $variationData['stock_quantity'];
                $variation->update([
                    'stock_quantity' => $stockQty,
                    'in_stock' => $stockQty > 0
                ]);
                $updated[] = [
                    'variation_id' => $variation->id,
                    'stock_quantity' => $variation->stock_quantity
                ];
            } else {
                $failed[] = [
                    'variation_id' => $variationData['variation_id'],
                    'reason' => 'Variation not found or not owned by vendor'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk stock update completed',
            'data' => [
                'updated' => $updated,
                'failed' => $failed,
                'total_updated' => count($updated),
                'total_failed' => count($failed)
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/orders",
     *     summary="Get vendor orders",
     *     description="Returns list of orders containing vendor's products",
     *     operationId="getVendorOrders",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", description="Filter by status", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function orders(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $vendorOrders = $this->getVendorOrders($vendor->id);
        $orders = $vendorOrders['all'];

        // Apply status filter
        if ($request->has('status')) {
            $orders = $orders->where('status', $request->status);
        }

        // Paginate
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 15), 50);
        $total = $orders->count();
        $orders = $orders->forPage($page, $perPage)->values();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/orders/{id}",
     *     summary="Get order details",
     *     description="Returns details of a specific order",
     *     operationId="getVendorOrderDetails",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Order/Invoice ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function orderDetails(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $invoice = ProformaInvoice::with('user')->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if this order contains vendor's products
        $invoiceData = $invoice->invoice_data;
        $vendorItems = [];
        $vendorTotal = 0;

        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                $productId = $item['product_id'] ?? $item['id'] ?? null;
                if ($productId) {
                    $product = Product::find($productId);
                    if ($product && $product->vendor_id == $vendor->id) {
                        $vendorItems[] = $item;
                        $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                    }
                }
            }
        }

        if (empty($vendorItems)) {
            return response()->json([
                'success' => false,
                'message' => 'This order does not contain your products'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order details retrieved successfully',
            'data' => [
                'order' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ],
                'customer' => [
                    'id' => $invoice->user->id ?? null,
                    'name' => $invoice->user->name ?? 'Guest',
                    'email' => $invoice->user->email ?? null,
                ],
                'vendor_items' => $vendorItems,
                'vendor_total' => round($vendorTotal, 2),
                'shipping_address' => $invoiceData['shipping_address'] ?? null,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/analytics",
     *     summary="Get vendor analytics",
     *     description="Returns detailed analytics data for the vendor",
     *     operationId="getVendorAnalytics",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="period", in="query", description="Period (weekly, monthly, yearly)", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function analytics(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $period = $request->get('period', 'monthly');
        $vendorOrders = $this->getVendorOrders($vendor->id);

        // Revenue chart data
        if ($period === 'yearly') {
            $revenueData = $this->getYearlyRevenueData($vendorOrders['delivered']);
        } elseif ($period === 'weekly') {
            $revenueData = $this->getWeeklyRevenueData($vendorOrders['delivered']);
        } else {
            $revenueData = $this->getMonthlyRevenueData($vendorOrders['delivered']);
        }

        // Order status distribution
        $orderStatusData = [
            ['status' => 'Draft', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DRAFT)->count()],
            ['status' => 'Approved', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_APPROVED)->count()],
            ['status' => 'Dispatch', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DISPATCH)->count()],
            ['status' => 'Out for Delivery', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_OUT_FOR_DELIVERY)->count()],
            ['status' => 'Delivered', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DELIVERED)->count()],
            ['status' => 'Return', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_RETURN)->count()],
        ];

        // Top selling products
        $topProducts = $this->getTopSellingProducts($vendorOrders['delivered'], $vendor->id);

        return response()->json([
            'success' => true,
            'message' => 'Analytics data retrieved successfully',
            'data' => [
                'revenue_chart' => $revenueData,
                'order_status_distribution' => $orderStatusData,
                'top_selling_products' => $topProducts,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/low-stock-products",
     *     summary="Get low stock products",
     *     description="Returns list of products with low stock",
     *     operationId="getLowStockProducts",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function lowStockProducts(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        // Get all products with their variations for this vendor
        // Note: mainPhoto is an accessor on Product, not a relationship
        $allProducts = Product::with(['variations'])
            ->where('vendor_id', $vendor->id)
            ->get();
        
        $lowStockItems = [];
        
        foreach ($allProducts as $product) {
            if ($product->product_type === 'variable' || $product->isVariable()) {
                // For variable products, check each variation
                foreach ($product->variations as $variation) {
                    $threshold = $variation->low_quantity_threshold ?? $product->low_quantity_threshold ?? 10;
                    if ($variation->stock_quantity <= $threshold) {
                        $lowStockItems[] = [
                            'type' => 'variation',
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'variation_id' => $variation->id,
                            'variation_sku' => $variation->sku,
                            'variation_attributes' => $variation->formatted_attributes,
                            'stock_quantity' => $variation->stock_quantity,
                            'low_quantity_threshold' => $threshold,
                            'in_stock' => $variation->in_stock,
                            'mrp' => $variation->mrp,
                            'selling_price' => $variation->selling_price,
                            'main_photo_url' => $product->mainPhoto?->url ?? null,
                        ];
                    }
                }
            } else {
                // For simple products
                $threshold = $product->low_quantity_threshold ?? 10;
                if ($product->in_stock && $product->stock_quantity <= $threshold) {
                    $lowStockItems[] = [
                        'type' => 'simple',
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'variation_id' => null,
                        'variation_sku' => null,
                        'variation_attributes' => null,
                        'stock_quantity' => $product->stock_quantity,
                        'low_quantity_threshold' => $threshold,
                        'in_stock' => $product->in_stock,
                        'mrp' => $product->mrp,
                        'selling_price' => $product->selling_price,
                        'main_photo_url' => $product->mainPhoto?->url ?? null,
                    ];
                }
            }
        }
        
        // Sort by stock quantity ascending
        usort($lowStockItems, function($a, $b) {
            return $a['stock_quantity'] - $b['stock_quantity'];
        });

        return response()->json([
            'success' => true,
            'message' => 'Low stock products retrieved successfully',
            'data' => [
                'total_count' => count($lowStockItems),
                'items' => $lowStockItems
            ]
        ]);
    }

    /**
     * Helper method to get vendor orders
     */
    private function getVendorOrders($vendorId)
    {
        $allInvoices = ProformaInvoice::whereNotNull('invoice_data')->get();
        
        $vendorOrders = collect();
        
        foreach ($allInvoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            $vendorTotal = 0;
            $hasVendorProducts = false;
            
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $hasVendorProducts = true;
                            $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                        }
                    }
                }
            }
            
            if ($hasVendorProducts) {
                $vendorOrders->push([
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-' . $invoice->id,
                    'user' => $invoice->user ? [
                        'id' => $invoice->user->id,
                        'name' => $invoice->user->name,
                        'email' => $invoice->user->email,
                    ] : null,
                    'status' => $invoice->status,
                    'vendor_total' => round($vendorTotal, 2),
                    'created_at' => $invoice->created_at,
                ]);
            }
        }
        
        return [
            'all' => $vendorOrders->sortByDesc('created_at'),
            'delivered' => $vendorOrders->where('status', ProformaInvoice::STATUS_DELIVERED),
            'pending' => $vendorOrders->whereIn('status', [
                ProformaInvoice::STATUS_DRAFT,
                ProformaInvoice::STATUS_APPROVED,
                ProformaInvoice::STATUS_DISPATCH,
                ProformaInvoice::STATUS_OUT_FOR_DELIVERY
            ]),
        ];
    }

    /**
     * Helper method to get monthly revenue data
     */
    private function getMonthlyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    $orderDate = Carbon::parse($order['created_at']);
                    return $orderDate->month === $date->month && $orderDate->year === $date->year;
                })
                ->sum('vendor_total');
            
            $data[] = [
                'label' => $date->format('M Y'),
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Helper method to get weekly revenue data
     */
    private function getWeeklyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    return Carbon::parse($order['created_at'])->isSameDay($date);
                })
                ->sum('vendor_total');
            
            $data[] = [
                'label' => $date->format('D, M d'),
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Helper method to get yearly revenue data
     */
    private function getYearlyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $revenue = $deliveredOrders
                ->filter(function($order) use ($year) {
                    return Carbon::parse($order['created_at'])->year === $year;
                })
                ->sum('vendor_total');
            
            $data[] = [
                'label' => (string)$year,
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Helper method to get top selling products
     */
    private function getTopSellingProducts($deliveredOrders, $vendorId)
    {
        $productSales = [];
        
        foreach ($deliveredOrders as $order) {
            $invoice = ProformaInvoice::find($order['id']);
            if (!$invoice) continue;
            
            $invoiceData = $invoice->invoice_data;
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $productName = $item['name'] ?? $item['product_name'] ?? 'Unknown Product';
                            $quantity = $item['quantity'] ?? 1;
                            $total = $item['total'] ?? ($item['price'] ?? 0) * $quantity;
                            
                            if (!isset($productSales[$productId])) {
                                $productSales[$productId] = [
                                    'id' => $productId,
                                    'name' => $productName,
                                    'quantity_sold' => 0,
                                    'revenue' => 0
                                ];
                            }
                            $productSales[$productId]['quantity_sold'] += $quantity;
                            $productSales[$productId]['revenue'] += $total;
                        }
                    }
                }
            }
        }
        
        usort($productSales, function($a, $b) {
            return $b['quantity_sold'] - $a['quantity_sold'];
        });
        
        return array_slice(array_values($productSales), 0, 10);
    }

    // =============================================
    // PRODUCT ATTRIBUTES MANAGEMENT
    // =============================================

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/attributes",
     *     summary="Get all product attributes",
     *     description="Returns list of all product attributes with their values",
     *     operationId="getVendorAttributes",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getAttributes(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $attributes = ProductAttribute::with('values')
            ->where('vendor_id', $vendor->id)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Attributes retrieved successfully',
            'data' => $attributes
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/attributes/{id}",
     *     summary="Get a specific product attribute",
     *     description="Returns a specific product attribute with its values",
     *     operationId="getVendorAttribute",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Attribute ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Attribute not found")
     * )
     */
    public function getAttribute(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $attribute = ProductAttribute::with('values')
            ->where('vendor_id', $vendor->id)
            ->find($id);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attribute retrieved successfully',
            'data' => $attribute
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/attributes",
     *     summary="Create a new product attribute",
     *     description="Create a new product attribute with values",
     *     operationId="createVendorAttribute",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","values"},
     *             @OA\Property(property="name", type="string", example="Color"),
     *             @OA\Property(property="description", type="string", example="Product color options"),
     *             @OA\Property(property="sort_order", type="integer", example=0),
     *             @OA\Property(property="values", type="array", @OA\Items(type="string"), example={"Red", "Blue", "Green"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Attribute created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createAttribute(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        if (!$vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Your vendor account must be approved to create attributes'
            ], 403);
        }

        // Filter out empty values
        $values = array_filter($request->values ?? [], function($value) {
            return !empty(trim($value));
        });

        $validator = Validator::make(array_merge($request->all(), ['values' => $values]), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            // Generate unique slug for this vendor
            $slug = \Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;
            while (ProductAttribute::where('vendor_id', $vendor->id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // Create attribute
            $attribute = ProductAttribute::create([
                'vendor_id' => $vendor->id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => true,
            ]);

            // Create attribute values
            $sortOrder = 0;
            foreach ($values as $value) {
                $attribute->values()->create([
                    'value' => trim($value),
                    'sort_order' => $sortOrder++,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Attribute created successfully',
                'data' => $attribute->load('values')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating attribute via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attribute: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/attributes/{id}",
     *     summary="Update a product attribute",
     *     description="Update an existing product attribute and its values",
     *     operationId="updateVendorAttribute",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Attribute ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Color"),
     *             @OA\Property(property="description", type="string", example="Product color options"),
     *             @OA\Property(property="sort_order", type="integer", example=0),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="values", type="array", @OA\Items(type="string"), example={"Red", "Blue", "Green"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Attribute updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Attribute not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateAttribute(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $attribute = ProductAttribute::find($id);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute not found'
            ], 404);
        }

        // Check vendor ownership
        if ($attribute->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this attribute'
            ], 403);
        }

        // Filter out empty values if provided
        $values = $request->has('values') 
            ? array_filter($request->values ?? [], function($value) {
                return !empty(trim($value));
            })
            : null;

        $validationRules = [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('product_attributes', 'name')
                    ->where('vendor_id', $vendor->id)
                    ->ignore($attribute->id)
            ],
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ];

        if ($values !== null) {
            $validationRules['values'] = 'required|array|min:1';
            $validationRules['values.*'] = 'required|string|max:255';
        }

        $validator = Validator::make(
            $values !== null ? array_merge($request->all(), ['values' => $values]) : $request->all(),
            $validationRules
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            // Update attribute
            $updateData = [];
            if ($request->has('name')) $updateData['name'] = $request->name;
            if ($request->has('description')) $updateData['description'] = $request->description;
            if ($request->has('sort_order')) $updateData['sort_order'] = $request->sort_order ?? 0;

            if (!empty($updateData)) {
                $attribute->update($updateData);
            }

            // Update values if provided
            if ($values !== null) {
                // Delete existing values
                $attribute->values()->delete();

                // Create new values
                $sortOrder = 0;
                foreach ($values as $value) {
                    $attribute->values()->create([
                        'value' => trim($value),
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Attribute updated successfully',
                'data' => $attribute->fresh()->load('values')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating attribute via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attribute: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendor/attributes/{id}",
     *     summary="Delete a product attribute",
     *     description="Delete a product attribute and all its values",
     *     operationId="deleteVendorAttribute",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Attribute ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Attribute deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Attribute not found")
     * )
     */
    public function deleteAttribute(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $attribute = ProductAttribute::find($id);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute not found'
            ], 404);
        }

        // Check vendor ownership
        if ($attribute->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this attribute'
            ], 403);
        }

        try {
            // Delete attribute (values will be cascade deleted if foreign key is set)
            $attribute->values()->delete();
            $attribute->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attribute deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting attribute via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attribute: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/attributes/{id}/values",
     *     summary="Add a value to an attribute",
     *     description="Add a new value to an existing product attribute",
     *     operationId="addAttributeValue",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Attribute ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"value"},
     *             @OA\Property(property="value", type="string", example="Purple"),
     *             @OA\Property(property="color_code", type="string", example="#800080"),
     *             @OA\Property(property="sort_order", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Value added successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Attribute not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addAttributeValue(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $attribute = ProductAttribute::find($id);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute not found'
            ], 404);
        }

        // Check vendor ownership
        if ($attribute->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this attribute'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $attributeValue = $attribute->values()->create([
                'value' => $request->value,
                'color_code' => $request->color_code,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Value added successfully',
                'data' => $attributeValue
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error adding attribute value via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add value: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/attributes/{id}/values/{valueId}",
     *     summary="Update an attribute value",
     *     description="Update an existing attribute value",
     *     operationId="updateAttributeValue",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Attribute ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="valueId", in="path", description="Value ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="value", type="string", example="Purple"),
     *             @OA\Property(property="color_code", type="string", example="#800080"),
     *             @OA\Property(property="sort_order", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Value updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Attribute or value not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateAttributeValue(Request $request, $id, $valueId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $attribute = ProductAttribute::find($id);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute not found'
            ], 404);
        }

        // Check vendor ownership
        if ($attribute->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this attribute'
            ], 403);
        }

        $value = $attribute->values()->find($valueId);

        if (!$value) {
            return response()->json([
                'success' => false,
                'message' => 'Value not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'sometimes|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [];
            if ($request->has('value')) $updateData['value'] = $request->value;
            if ($request->has('color_code')) $updateData['color_code'] = $request->color_code;
            if ($request->has('sort_order')) $updateData['sort_order'] = $request->sort_order;

            $value->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Value updated successfully',
                'data' => $value->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating attribute value via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update value: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendor/attributes/{id}/values/{valueId}",
     *     summary="Delete an attribute value",
     *     description="Delete an attribute value",
     *     operationId="deleteAttributeValue",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Attribute ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="valueId", in="path", description="Value ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Value deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Attribute or value not found")
     * )
     */
    public function deleteAttributeValue(Request $request, $id, $valueId)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $attribute = ProductAttribute::find($id);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute not found'
            ], 404);
        }

        // Check vendor ownership
        if ($attribute->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this attribute'
            ], 403);
        }

        $value = $attribute->values()->find($valueId);

        if (!$value) {
            return response()->json([
                'success' => false,
                'message' => 'Value not found'
            ], 404);
        }

        try {
            $value->delete();

            return response()->json([
                'success' => true,
                'message' => 'Value deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting attribute value via API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete value: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================
    // VENDOR STAFF MANAGEMENT
    // =============================================

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/staff",
     *     summary="Get all vendor staff",
     *     description="Returns list of all staff members for the vendor",
     *     operationId="getVendorStaff",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getStaff(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($staffMember) {
                // Remove duplicate permissions
                $permissions = $staffMember->permissions ?? [];
                $uniquePermissions = array_values(array_unique($permissions));
                
                return [
                    'id' => $staffMember->id,
                    'user_id' => $staffMember->user_id,
                    'name' => $staffMember->user->name ?? null,
                    'email' => $staffMember->user->email ?? null,
                    'mobile_number' => $staffMember->user->mobile_number ?? null,
                    'role' => $staffMember->role,
                    'permissions' => $uniquePermissions,
                    'is_active' => $staffMember->is_active,
                    'created_at' => $staffMember->created_at,
                    'updated_at' => $staffMember->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Staff retrieved successfully',
            'data' => $staff
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/staff/permissions",
     *     summary="Get available staff permissions",
     *     description="Returns list of all available permissions for vendor staff",
     *     operationId="getStaffPermissions",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getStaffPermissions(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $permissions = [
            'dashboard' => 'Dashboard',
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

            'profile' => 'Profile',
            'store_settings' => 'Store Settings',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Permissions retrieved successfully',
            'data' => $permissions
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/staff",
     *     summary="Create a new staff member",
     *     description="Create a new staff member for the vendor",
     *     operationId="createVendorStaff",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="staff@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="mobile_number", type="string", example="9876543210"),
     *             @OA\Property(property="role", type="string", example="manager"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"dashboard", "products", "categories"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Staff created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createStaff(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'required|string|max:50',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        \DB::beginTransaction();

        try {
            // Create user account with vendor_staff role
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile_number' => $request->mobile_number,
                'user_role' => 'vendor_staff',
                'is_approved' => true,
            ]);

            // Remove duplicate permissions
            $permissions = $request->permissions ?? [];
            $uniquePermissions = array_values(array_unique($permissions));

            // Create vendor staff record
            $staff = VendorStaff::create([
                'vendor_id' => $vendor->id,
                'user_id' => $user->id,
                'role' => $request->role,
                'permissions' => $uniquePermissions,
                'is_active' => true,
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff member created successfully',
                'data' => [
                    'id' => $staff->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $staff->role,
                    'permissions' => $uniquePermissions,
                    'is_active' => $staff->is_active,
                ]
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error creating staff via API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/staff/{id}",
     *     summary="Update a staff member",
     *     description="Update an existing staff member",
     *     operationId="updateVendorStaff",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Staff ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="staff@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="mobile_number", type="string", example="9876543210"),
     *             @OA\Property(property="role", type="string", example="manager"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Staff updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Staff not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateStaff(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $staff->user_id,
            'password' => 'nullable|string|min:8',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'sometimes|string|max:50',
            'permissions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        \DB::beginTransaction();

        try {
            // Update user account
            $userData = [];
            if ($request->has('name')) $userData['name'] = $request->name;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->has('mobile_number')) $userData['mobile_number'] = $request->mobile_number;
            if ($request->filled('password')) $userData['password'] = Hash::make($request->password);

            if (!empty($userData)) {
                $staff->user->update($userData);
            }

            // Update vendor staff record
            $staffData = [];
            if ($request->has('role')) $staffData['role'] = $request->role;
            if ($request->has('is_active')) $staffData['is_active'] = $request->is_active;
            
            // Remove duplicate permissions
            if ($request->has('permissions')) {
                $permissions = $request->permissions ?? [];
                $staffData['permissions'] = array_values(array_unique($permissions));
            }

            if (!empty($staffData)) {
                $staff->update($staffData);
            }

            \DB::commit();

            $staff = $staff->fresh();
            $staff->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Staff member updated successfully',
                'data' => [
                    'id' => $staff->id,
                    'user_id' => $staff->user_id,
                    'name' => $staff->user->name,
                    'email' => $staff->user->email,
                    'mobile_number' => $staff->user->mobile_number,
                    'role' => $staff->role,
                    'permissions' => $staff->permissions,
                    'is_active' => $staff->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error updating staff via API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendor/staff/{id}",
     *     summary="Delete a staff member",
     *     description="Delete a staff member",
     *     operationId="deleteVendorStaff",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Staff ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Staff deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Staff not found")
     * )
     */
    public function deleteStaff(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $staff = VendorStaff::where('vendor_id', $vendor->id)->find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        \DB::beginTransaction();

        try {
            // Delete the user account
            if ($staff->user) {
                $staff->user->delete();
            }

            // Delete the vendor staff record
            $staff->delete();

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff member deleted successfully'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error deleting staff via API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/staff/{id}/toggle-status",
     *     summary="Toggle staff active status",
     *     description="Toggle a staff member's active status",
     *     operationId="toggleStaffStatus",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", description="Staff ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Status toggled successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Staff not found")
     * )
     */
    public function toggleStaffStatus(Request $request, $id)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        $staff = VendorStaff::where('vendor_id', $vendor->id)->find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        $staff->update(['is_active' => !$staff->is_active]);

        return response()->json([
            'success' => true,
            'message' => $staff->is_active ? 'Staff member activated' : 'Staff member deactivated',
            'data' => [
                'id' => $staff->id,
                'is_active' => $staff->is_active
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/staff/dashboard-sections",
     *     summary="Get dashboard sections for staff",
     *     description="Returns dashboard sections based on staff permissions",
     *     operationId="getStaffDashboardSections",
     *     tags={"Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getStaffDashboardSections(Request $request)
    {
        $user = $request->user();
        $isVendorOwner = $user->isVendor();
        $staffPermissions = [];
        $vendor = null;

        if ($isVendorOwner) {
            $vendor = $user->vendor;
            // Vendor owners have all permissions
            $staffPermissions = [
                'dashboard', 'products', 'variations', 'attributes', 'categories',
                'invoices', 'pending_bills', 'leads', 'customers', 'staff',
                'salary', 'attendance', 'reports', 'analytics', 'coupons',
                'media', 'profile', 'store_settings'
            ];
        } elseif ($user->isVendorStaff()) {
            $staffRecord = $user->vendorStaff;
            if ($staffRecord) {
                // Remove duplicate permissions
                $staffPermissions = array_values(array_unique($staffRecord->permissions ?? []));
                $vendor = $staffRecord->vendor;
            }
        }

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }

        // Define all available sections with their details
        $allSections = [
            'dashboard' => ['name' => 'Dashboard', 'icon' => 'home', 'route' => 'vendor.dashboard'],
            'products' => ['name' => 'Products', 'icon' => 'box', 'route' => 'vendor.products.index'],
            'categories' => ['name' => 'Categories', 'icon' => 'tags', 'route' => 'vendor.categories.index'],
            'attributes' => ['name' => 'Product Attributes', 'icon' => 'sliders-h', 'route' => 'vendor.attributes.index'],
            'invoices' => ['name' => 'Invoices', 'icon' => 'file-invoice', 'route' => 'vendor.invoices.index'],
            'pending_bills' => ['name' => 'Pending Bills', 'icon' => 'file-invoice-dollar', 'route' => 'vendor.pending-bills.index'],
            'leads' => ['name' => 'Leads', 'icon' => 'user-plus', 'route' => 'vendor.leads.index'],
            'customers' => ['name' => 'Customers', 'icon' => 'user-friends', 'route' => 'vendor.customers.index'],
            'staff' => ['name' => 'Staff', 'icon' => 'users', 'route' => 'vendor.staff.index'],
            'salary' => ['name' => 'Salary', 'icon' => 'money-bill-wave', 'route' => 'vendor.salary.index'],
            'attendance' => ['name' => 'Attendance', 'icon' => 'calendar-check', 'route' => 'vendor.attendance.index'],
            'reports' => ['name' => 'Reports', 'icon' => 'chart-bar', 'route' => 'vendor.reports.index'],
            'analytics' => ['name' => 'Product Analytics', 'icon' => 'chart-line', 'route' => 'vendor.analytics.products'],
            'coupons' => ['name' => 'Coupons', 'icon' => 'ticket-alt', 'route' => 'vendor.coupons.index'],

            'profile' => ['name' => 'Profile', 'icon' => 'user', 'route' => 'vendor.profile.index'],
            'store_settings' => ['name' => 'Store Settings', 'icon' => 'store', 'route' => 'vendor.profile.store'],
        ];

        // Filter sections based on permissions
        $availableSections = [];
        foreach ($staffPermissions as $permission) {
            if (isset($allSections[$permission])) {
                $availableSections[$permission] = $allSections[$permission];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard sections retrieved successfully',
            'data' => [
                'is_vendor_owner' => $isVendorOwner,
                'permissions' => $staffPermissions,
                'sections' => $availableSections,
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'status' => $vendor->status,
                ]
            ]
        ]);
    }
}
