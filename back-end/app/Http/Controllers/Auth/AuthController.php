<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\OTP\OTPService;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $otpService;
    protected $authService;

    public function __construct(OTPService $otpService, AuthService $authService)
    {
        $this->otpService = $otpService;
        $this->authService = $authService;
    }

    /**
     *Request OTP to Login
     */
    public function requestLogin(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            // Verify credentials
            $user = $this->authService->verifyCredentials($request->login, $request->password);

            // Generate and send OTP
            $this->otpService->generate($user, 'login', $request->login);

            $isEmail = filter_var($request->login, FILTER_VALIDATE_EMAIL);
            $message = $isEmail ? "OTP sent to your email." : "OTP sent to your phone.";

            return response()->json([
                'status'  => 'success',
                'message' => $message
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode();
            $statusCode = ($statusCode >= 400 && $statusCode <= 599) ? $statusCode : 401;

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     *Verify OTP
     */
    public function verifyLogin(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'otp_code' => ['required', 'string', 'size:6']
        ]);

        try {
            // Find user by identifier
            $user = $this->authService->findUserByLogin($request->login);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.'
                ], 404);
            }

            // Verify the OTP code
            $isValid = $this->otpService->verify($user, $request->otp_code, 'login');

            if (!$isValid) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Invalid or expired OTP."
                ], 401);
            }

            // Create Sanctum Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'  => 'success',
                'message' => 'Login successful.',
                'token'   => $token,
                'user'    => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Logout (Revoke Token)
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Logged out successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to logout.'
            ], 500);
        }
    }
}

