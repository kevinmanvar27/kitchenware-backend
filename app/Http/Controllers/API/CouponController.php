<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Coupon;
use App\Models\ShoppingCartItem;

/**
 * @OA\Tag(
 *     name="Coupons",
 *     description="API Endpoints for Coupon Management"
 * )
 */
class CouponController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/coupons/validate",
     *     summary="Validate a coupon code",
     *     description="Check if a coupon code is valid and calculate the discount",
     *     operationId="validateCoupon",
     *     tags={"Coupons"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="SAVE20"),
     *             @OA\Property(property="cart_total", type="number", format="float", example=1000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon validation result",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon is valid"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="coupon", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="SAVE20"),
     *                     @OA\Property(property="discount_type", type="string", example="percentage"),
     *                     @OA\Property(property="discount_value", type="number", example=20),
     *                     @OA\Property(property="min_order_amount", type="number", example=500),
     *                     @OA\Property(property="max_discount_amount", type="number", example=200)
     *                 ),
     *                 @OA\Property(property="discount_amount", type="number", example=200),
     *                 @OA\Property(property="final_total", type="number", example=800)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid coupon"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function validateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'cart_total' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 400);
        }

        // Check if coupon can be used by this user
        $user = $request->user();
        $validationResult = $coupon->canBeUsedByWithMessage($user);

        if (!$validationResult['can_use']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message']
            ], 400);
        }

        // Calculate cart total if not provided
        $cartTotal = $request->cart_total;
        if (!$cartTotal && $user) {
            $cartItems = ShoppingCartItem::where('user_id', $user->id)->get();
            $cartTotal = $cartItems->sum(function($item) {
                return ($item->price ?? 0) * ($item->quantity ?? 1);
            });
        }

        // Check minimum order amount
        if ($cartTotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order amount of ₹' . number_format($coupon->min_order_amount, 2) . ' is required to use this coupon'
            ], 400);
        }

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($cartTotal);
        $finalTotal = $cartTotal - $discountAmount;

        return response()->json([
            'success' => true,
            'message' => 'Coupon is valid',
            'data' => [
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'formatted_discount' => $coupon->formatted_discount,
                    'min_order_amount' => $coupon->min_order_amount,
                    'max_discount_amount' => $coupon->max_discount_amount,
                ],
                'cart_total' => round($cartTotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'final_total' => round($finalTotal, 2),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/coupons/apply",
     *     summary="Apply a coupon to cart",
     *     description="Apply a coupon code to the user's cart",
     *     operationId="applyCoupon",
     *     tags={"Coupons"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="SAVE20")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Coupon applied successfully"),
     *     @OA\Response(response=400, description="Invalid coupon"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 400);
        }

        $user = $request->user();
        $validationResult = $coupon->canBeUsedByWithMessage($user);

        if (!$validationResult['can_use']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message']
            ], 400);
        }

        // Get cart total
        $cartItems = ShoppingCartItem::where('user_id', $user->id)->get();
        $cartTotal = $cartItems->sum(function($item) {
            return ($item->price ?? 0) * ($item->quantity ?? 1);
        });

        if ($cartTotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order amount of ₹' . number_format($coupon->min_order_amount, 2) . ' is required'
            ], 400);
        }

        // Store coupon in session/user preference
        $user->update(['applied_coupon_id' => $coupon->id]);

        $discountAmount = $coupon->calculateDiscount($cartTotal);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully',
            'data' => [
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'formatted_discount' => $coupon->formatted_discount,
                ],
                'cart_total' => round($cartTotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'final_total' => round($cartTotal - $discountAmount, 2),
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/coupons/remove",
     *     summary="Remove applied coupon",
     *     description="Remove the currently applied coupon from cart",
     *     operationId="removeCoupon",
     *     tags={"Coupons"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Coupon removed successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function remove(Request $request)
    {
        $user = $request->user();
        $user->update(['applied_coupon_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/coupons/applied",
     *     summary="Get currently applied coupon",
     *     description="Get the coupon currently applied to the user's cart",
     *     operationId="getAppliedCoupon",
     *     tags={"Coupons"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Applied coupon details or null if none applied",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", nullable=true,
     *                 @OA\Property(property="coupon", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="SAVE20"),
     *                     @OA\Property(property="formatted_discount", type="string", example="20%")
     *                 ),
     *                 @OA\Property(property="discount_amount", type="number", example=200),
     *                 @OA\Property(property="cart_total", type="number", example=1000),
     *                 @OA\Property(property="final_total", type="number", example=800)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getApplied(Request $request)
    {
        $user = $request->user();
        
        if (!$user->applied_coupon_id) {
            return response()->json([
                'success' => true,
                'message' => 'No coupon applied',
                'data' => null
            ]);
        }
        
        $coupon = Coupon::find($user->applied_coupon_id);
        
        if (!$coupon || !$coupon->isValid() || !$coupon->canBeUsedBy($user)) {
            // Clear invalid coupon
            $user->update(['applied_coupon_id' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'No valid coupon applied',
                'data' => null
            ]);
        }
        
        // Get cart total
        $cartItems = ShoppingCartItem::where('user_id', $user->id)->get();
        $cartTotal = $cartItems->sum(function($item) {
            return ($item->price ?? 0) * ($item->quantity ?? 1);
        });
        
        // Check minimum order amount
        if ($cartTotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon applied but minimum order amount not met',
                'data' => [
                    'coupon' => [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'description' => $coupon->description,
                        'formatted_discount' => $coupon->formatted_discount,
                        'min_order_amount' => $coupon->min_order_amount,
                    ],
                    'cart_total' => round($cartTotal, 2),
                    'discount_amount' => 0,
                    'final_total' => round($cartTotal, 2),
                    'min_order_not_met' => true,
                    'min_order_shortfall' => round($coupon->min_order_amount - $cartTotal, 2),
                ]
            ]);
        }
        
        $discountAmount = $coupon->calculateDiscount($cartTotal);
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied',
            'data' => [
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'formatted_discount' => $coupon->formatted_discount,
                    'min_order_amount' => $coupon->min_order_amount,
                    'max_discount_amount' => $coupon->max_discount_amount,
                ],
                'cart_total' => round($cartTotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'final_total' => round($cartTotal - $discountAmount, 2),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/coupons/available",
     *     summary="Get available coupons",
     *     description="Get list of available coupons for the user",
     *     operationId="getAvailableCoupons",
     *     tags={"Coupons"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of available coupons",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="SAVE20"),
     *                     @OA\Property(property="description", type="string", example="Get 20% off"),
     *                     @OA\Property(property="discount_type", type="string", example="percentage"),
     *                     @OA\Property(property="discount_value", type="number", example=20),
     *                     @OA\Property(property="min_order_amount", type="number", example=500),
     *                     @OA\Property(property="valid_until", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function available(Request $request)
    {
        $user = $request->user();

        $coupons = Coupon::active()
            ->valid()
            ->notExhausted()
            ->get()
            ->filter(function($coupon) use ($user) {
                return $coupon->canBeUsedBy($user);
            })
            ->map(function($coupon) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'formatted_discount' => $coupon->formatted_discount,
                    'min_order_amount' => $coupon->min_order_amount,
                    'max_discount_amount' => $coupon->max_discount_amount,
                    'valid_until' => $coupon->valid_until,
                    'status' => $coupon->status,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Available coupons retrieved successfully',
            'data' => $coupons
        ]);
    }
}
