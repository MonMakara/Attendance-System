<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\OTP\OTPService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    protected $otpService;
    protected $authService;

    public function __construct(OTPService $otpService, AuthService $authService)
    {
        $this->otpService = $otpService;
        $this->authService = $authService;
    }

    /**
     * Forgot Password
     * Request Reset OTP
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Email or Phone
        ]);

        $user = $this->authService->findUserByLogin($request->login);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        try {
            // Generate OTP for password reset
            $this->otpService->generate($user, 'password_reset', $request->login);

            $isEmail = filter_var($request->login, FILTER_VALIDATE_EMAIL);
            $message = $isEmail ? "Reset code sent to your email." : "Reset code sent to your phone.";

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send reset code.'
            ], 500);
        }
    }

    /**
     * Forgot Password
     * Verify OTP & Reset Password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'otp_code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = $this->authService->findUserByLogin($request->login);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        try {
            // Verify the OTP (will throw exception if max attempts reached)
            $isValid = $this->otpService->verify($user, $request->otp_code, 'password_reset');

            if (!$isValid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired reset code.'
                ], 401);
            }

            // Update Password
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully. You can now log in.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset password.'
            ], 500);
        }
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The current password provided is incorrect.'
            ], 401);
        }

        try {
            // Update to new password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update password.'
            ], 500);
        }
    }
}
