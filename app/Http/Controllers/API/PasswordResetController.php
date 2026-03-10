<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Password Reset",
 *     description="API Endpoints for Password Reset with OTP"
 * )
 */
class PasswordResetController extends ApiController
{
    /**
     * Request password reset (forgot password) - Sends OTP
     * 
     * @OA\Post(
     *      path="/api/v1/forgot-password",
     *      operationId="forgotPassword",
     *      tags={"Password Reset"},
     *      summary="Request password reset OTP",
     *      description="Send password reset OTP to user's email",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OTP sent successfully",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      ),
     *      @OA\Response(
     *          response=429,
     *          description="Too many requests"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if a recent OTP exists (rate limiting - 1 minute cooldown)
        $recentToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('created_at', '>', Carbon::now()->subMinutes(1))
            ->first();

        if ($recentToken) {
            $remainingSeconds = 60 - Carbon::parse($recentToken->created_at)->diffInSeconds(Carbon::now());
            return $this->sendError("Please wait {$remainingSeconds} seconds before requesting another OTP.", [], 429);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Store new OTP (hashed for security)
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($otp),
            'created_at' => Carbon::now(),
        ]);

        // Send email with OTP (only if user exists)
        if ($user) {
            try {
                $this->sendOtpEmail($user, $otp);
            } catch (\Exception $e) {
                \Log::error('Password reset OTP email failed: ' . $e->getMessage());
                // Don't return error - still show success for security
            }
        }

        // Always return success response (prevents email enumeration)
        return $this->sendResponse([
            'email' => $this->maskEmail($request->email),
            'expires_in' => 10, // minutes
        ], 'If an account exists with this email, an OTP has been sent.');
    }

    /**
     * Verify OTP
     * 
     * @OA\Post(
     *      path="/api/v1/verify-otp",
     *      operationId="verifyOtp",
     *      tags={"Password Reset"},
     *      summary="Verify OTP",
     *      description="Verify the OTP sent to user's email",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email", "otp"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *              @OA\Property(property="otp", type="string", example="123456")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OTP verified successfully",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid or expired OTP"
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
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        // Find the token record
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return $this->sendError('Invalid or expired OTP. Please request a new one.', [], 400);
        }

        // Check if OTP is expired (10 minutes)
        if (Carbon::parse($tokenRecord->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->sendError('OTP has expired. Please request a new one.', [], 400);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $tokenRecord->token)) {
            return $this->sendError('Invalid OTP. Please check and try again.', [], 400);
        }

        // Generate a reset token for the next step
        $resetToken = Str::random(64);
        
        // Update the record with reset token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->update([
                'token' => Hash::make($resetToken),
                'created_at' => Carbon::now(), // Reset expiry
            ]);

        return $this->sendResponse([
            'reset_token' => $resetToken,
            'expires_in' => 10, // minutes
        ], 'OTP verified successfully. You can now reset your password.');
    }

    /**
     * Resend OTP
     * 
     * @OA\Post(
     *      path="/api/v1/resend-otp",
     *      operationId="resendOtp",
     *      tags={"Password Reset"},
     *      summary="Resend OTP",
     *      description="Resend OTP to user's email",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OTP resent successfully",
     *       ),
     *      @OA\Response(
     *          response=429,
     *          description="Too many requests"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendOtp(Request $request)
    {
        return $this->forgotPassword($request);
    }

    /**
     * Reset password with token
     * 
     * @OA\Post(
     *      path="/api/v1/reset-password",
     *      operationId="resetPassword",
     *      tags={"Password Reset"},
     *      summary="Reset password",
     *      description="Reset password using token received after OTP verification",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email", "token", "password", "password_confirmation"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *              @OA\Property(property="token", type="string", example="abc123..."),
     *              @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Password reset successful",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid or expired token"
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
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the token record
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return $this->sendError('Invalid or expired reset token.', [], 400);
        }

        // Check if token is expired (10 minutes)
        if (Carbon::parse($tokenRecord->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->sendError('Reset token has expired. Please start over.', [], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return $this->sendError('Invalid or expired reset token.', [], 400);
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return $this->sendResponse(null, 'Password has been reset successfully. Please login with your new password.');
    }

    /**
     * Verify reset token (optional - for mobile apps to validate before showing reset form)
     * 
     * @OA\Post(
     *      path="/api/v1/verify-reset-token",
     *      operationId="verifyResetToken",
     *      tags={"Password Reset"},
     *      summary="Verify reset token",
     *      description="Verify if a password reset token is valid",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email", "token"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *              @OA\Property(property="token", type="string", example="abc123...")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Token is valid",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid or expired token"
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
    public function verifyResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        // Find the token record
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return $this->sendError('Invalid or expired reset token.', [], 400);
        }

        // Check if token is expired (10 minutes)
        if (Carbon::parse($tokenRecord->created_at)->addMinutes(10)->isPast()) {
            return $this->sendError('Reset token has expired. Please request a new one.', [], 400);
        }

        // Verify token
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return $this->sendError('Invalid or expired reset token.', [], 400);
        }

        return $this->sendResponse([
            'valid' => true,
            'expires_at' => Carbon::parse($tokenRecord->created_at)->addMinutes(10)->toIso8601String(),
        ], 'Token is valid.');
    }

    /**
     * Send OTP email to user
     * 
     * @param User $user
     * @param string $otp
     * @return void
     */
    private function sendOtpEmail(User $user, string $otp)
    {
        Mail::send('emails.password-reset-otp', [
            'user' => $user,
            'otp' => $otp,
            'expiresIn' => '10 minutes',
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('Password Reset OTP - ' . config('app.name'));
        });
    }

    /**
     * Mask email for privacy
     * 
     * @param string $email
     * @return string
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 4, 2)) . substr($name, -2);
        
        return $maskedName . '@' . $domain;
    }
}
