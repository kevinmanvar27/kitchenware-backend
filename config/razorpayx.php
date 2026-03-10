<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Razorpay X API Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are used for Razorpay X Payout functionality.
    | You can get these from your Razorpay X Dashboard.
    |
    */

    'key_id' => env('RAZORPAYX_KEY_ID'),
    'key_secret' => env('RAZORPAYX_KEY_SECRET'),
    'account_number' => env('RAZORPAYX_ACCOUNT_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Razorpay X Mode
    |--------------------------------------------------------------------------
    |
    | Set to 'test' for testing or 'live' for production.
    |
    */

    'mode' => env('RAZORPAYX_MODE', 'test'),

];
