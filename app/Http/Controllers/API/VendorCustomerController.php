<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\VendorCustomer;
use App\Models\Vendor;
use App\Models\ProformaInvoice;

/**
 * @OA\Tag(
 *     name="Vendor Customers",
 *     description="API Endpoints for Vendors to manage their customers"
 * )
 */
class VendorCustomerController extends Controller
{
    /**
     * Get the authenticated vendor
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
     * @OA\Get(
     *     path="/api/v1/vendor/customers",
     *     summary="Get vendor customers",
     *     description="Get list of customers created by the vendor",
     *     operationId="getVendorCustomers",
     *     tags={"Vendor Customers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Search by name, email, or mobile", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_active", in="query", description="Filter by active status", required=false, @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor")
     * )
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
                'data' => null
            ], 403);
        }

        $query = VendorCustomer::where('vendor_id', $vendor->id)
            ->whereNotNull('email'); // Only customers with login credentials

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('mobile_number', 'like', '%' . $search . '%');
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min($request->get('per_page', 20), 50);
        $customers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform the data
        $customers->getCollection()->transform(function($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'mobile_number' => $customer->mobile_number,
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'postal_code' => $customer->postal_code,
                'discount_percentage' => $customer->discount_percentage,
                'is_active' => $customer->is_active,
                'last_login_at' => $customer->last_login_at,
                'created_at' => $customer->created_at,
            ];
        });

        // Get statistics
        $totalCustomers = VendorCustomer::where('vendor_id', $vendor->id)->whereNotNull('email')->count();
        $activeCustomers = VendorCustomer::where('vendor_id', $vendor->id)->whereNotNull('email')->where('is_active', true)->count();
        $newThisMonth = VendorCustomer::where('vendor_id', $vendor->id)
            ->whereNotNull('email')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => [
                'customers' => $customers,
                'statistics' => [
                    'total_customers' => $totalCustomers,
                    'active_customers' => $activeCustomers,
                    'inactive_customers' => $totalCustomers - $activeCustomers,
                    'new_this_month' => $newThisMonth,
                ]
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vendor/customers",
     *     summary="Create a new customer",
     *     description="Create a new customer with login credentials. This customer can only see products from this vendor.",
     *     operationId="createVendorCustomer",
     *     tags={"Vendor Customers"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="customer@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123"),
     *             @OA\Property(property="mobile_number", type="string", example="9876543210"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="postal_code", type="string", example="400001"),
     *             @OA\Property(property="discount_percentage", type="number", format="float", example=5.00, description="Customer-specific discount percentage")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Customer created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
                'data' => null
            ], 403);
        }

        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        // Check if email already exists for this vendor
        $existingCustomer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('email', $request->email)
            ->first();

        if ($existingCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'A customer with this email already exists',
                'data' => null
            ], 422);
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

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
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
                    'is_active' => $customer->is_active,
                    'created_at' => $customer->created_at,
                ],
                'login_credentials' => [
                    'email' => $customer->email,
                    'password' => $request->password, // Return plain password once for vendor to share
                    'vendor_slug' => $vendor->store_slug,
                    'login_url' => '/api/v1/customer/login',
                ]
            ]
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vendor/customers/{id}",
     *     summary="Get customer details",
     *     description="Get detailed information about a specific customer",
     *     operationId="getVendorCustomerDetails",
     *     tags={"Vendor Customers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Customer ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor"),
     *     @OA\Response(response=404, description="Customer not found")
     * )
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
                'data' => null
            ], 403);
        }

        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        // Get customer statistics
        $totalOrders = ProformaInvoice::where('vendor_id', $vendor->id)
            ->where('vendor_customer_id', $customer->id)
            ->count();
            
        $totalSpent = ProformaInvoice::where('vendor_id', $vendor->id)
            ->where('vendor_customer_id', $customer->id)
            ->sum('total_amount');

        // Get recent orders
        $recentOrders = ProformaInvoice::where('vendor_id', $vendor->id)
            ->where('vendor_customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Customer retrieved successfully',
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
                    'is_active' => $customer->is_active,
                    'last_login_at' => $customer->last_login_at,
                    'created_at' => $customer->created_at,
                ],
                'statistics' => [
                    'total_orders' => $totalOrders,
                    'total_spent' => $totalSpent,
                ],
                'recent_orders' => $recentOrders,
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/customers/{id}",
     *     summary="Update customer",
     *     description="Update customer information",
     *     operationId="updateVendorCustomer",
     *     tags={"Vendor Customers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Customer ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="mobile_number", type="string", example="9876543210"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="Mumbai"),
     *             @OA\Property(property="state", type="string", example="Maharashtra"),
     *             @OA\Property(property="postal_code", type="string", example="400001"),
     *             @OA\Property(property="discount_percentage", type="number", format="float", example=5.00),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Customer updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor"),
     *     @OA\Response(response=404, description="Customer not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
                'data' => null
            ], 403);
        }

        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'mobile_number' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'discount_percentage' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
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
            'discount_percentage',
            'is_active',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
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
                    'is_active' => $customer->is_active,
                ]
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/customers/{id}/reset-password",
     *     summary="Reset customer password",
     *     description="Reset a customer's password",
     *     operationId="resetVendorCustomerPassword",
     *     tags={"Vendor Customers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Customer ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", format="password", example="NewPassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor"),
     *     @OA\Response(response=404, description="Customer not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function resetPassword(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
                'data' => null
            ], 403);
        }

        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        // Revoke all existing tokens
        $customer->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
            'data' => [
                'login_credentials' => [
                    'email' => $customer->email,
                    'password' => $request->password,
                    'vendor_slug' => $vendor->store_slug,
                ]
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vendor/customers/{id}",
     *     summary="Delete customer",
     *     description="Delete a customer (soft delete by deactivating)",
     *     operationId="deleteVendorCustomer",
     *     tags={"Vendor Customers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Customer ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Customer deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor"),
     *     @OA\Response(response=404, description="Customer not found")
     * )
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
                'data' => null
            ], 403);
        }

        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        // Revoke all tokens
        $customer->tokens()->delete();
        
        // Delete the customer
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
            'data' => null
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vendor/customers/{id}/toggle-status",
     *     summary="Toggle customer status",
     *     description="Activate or deactivate a customer",
     *     operationId="toggleVendorCustomerStatus",
     *     tags={"Vendor Customers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Customer ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Status toggled successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Not a vendor"),
     *     @OA\Response(response=404, description="Customer not found")
     * )
     */
    public function toggleStatus($id)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
                'data' => null
            ], 403);
        }

        $customer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        $customer->update([
            'is_active' => !$customer->is_active
        ]);

        // If deactivating, revoke all tokens
        if (!$customer->is_active) {
            $customer->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $customer->is_active ? 'Customer activated successfully' : 'Customer deactivated successfully',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'is_active' => $customer->is_active,
                ]
            ]
        ]);
    }
}
