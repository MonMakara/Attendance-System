<?php

namespace App\Services\OTP\Channels;

use App\Mails\OTPMail;
use Illuminate\Support\Facades\Mail;

class EmailChannel
{
    public function send($email, $code)
    {
        Mail::to($email)->send(new OTPMail($code));
    }
}
