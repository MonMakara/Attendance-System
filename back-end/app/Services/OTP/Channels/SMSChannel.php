<?php

namespace App\Services\OTP\Channels;

use Illuminate\Support\Facades\Log;

class SMSChannel
{
    public function send($phone, $code)
    {
        // Add your Twilio/Vonage logic here later
        Log::info("SMS SENT TO {$phone}: Your code is {$code}");
    }
}
