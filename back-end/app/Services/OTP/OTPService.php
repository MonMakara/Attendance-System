<?php

namespace App\Services\OTP;

use App\Models\User;
use App\Services\OTP\Channels\EmailChannel;
use App\Services\OTP\Channels\SMSChannel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OTPService
{
    protected $emailChannel;
    protected $smsChannel;

    public function __construct(EmailChannel $emailChannel, SMSChannel $smsChannel)
    {
        $this->emailChannel = $emailChannel;
        $this->smsChannel = $smsChannel;
    }

    // Generate OTP
    public function generate(User $user, string $purpose, ?string $identifier = null)
    {
        return DB::transaction(function () use ($user, $purpose, $identifier) {
            try {
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $deliveryIdentifier = $identifier ?? ($user->phone ?? $user->email);

                // Clean up any existing OTPs for this user and purpose
                $user->otps()
                    ->where('purpose', $purpose)
                    ->delete();

                $user->otps()->create([
                    'identifier' => $deliveryIdentifier,
                    'otp_hash' => Hash::make($code),
                    'purpose' => $purpose,
                    'expires_at' => Carbon::now()->addMinutes(10),
                    'attempts' => 0,
                    'is_used' => false,
                ]);
                if (filter_var($deliveryIdentifier, FILTER_VALIDATE_EMAIL)) {
                    $this->emailChannel->send($deliveryIdentifier, $code);
                } else {
                    $this->smsChannel->send($deliveryIdentifier, $code);
                }

                return $code;
            } catch (\Throwable $th) {
                Log::error("Error generating OTP: " . $th->getMessage());
                throw new \Exception("Failed to generate OTP.");
            }
        });
    }

    /**
     * Verify OTP
     */
    public function verify(User $user, $code, string $purpose)
    {
        $otpRecord = $user->otps()
            ->where('purpose', $purpose)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return false;
        }

        if (!Hash::check($code, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');
            
            // Limit attempts to 3
            if ($otpRecord->attempts >= 3) {
                $otpRecord->delete(); // Delete after 3 failed tries
                throw new \Exception("Maximum OTP attempts reached. Please request a new code.");
            }
            
            return false;
        }

        // OTP verified - delete it immediately
        $otpRecord->delete();

        return true;
    }
}