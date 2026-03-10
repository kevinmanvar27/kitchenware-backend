<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Razorpay Configuration (Customer Payments)
    |--------------------------------------------------------------------------
    |
    | Configuration for Razorpay Payment Gateway. Used for customer payments.
    | Primary values come from database settings, these are fallback values.
    |
    */
    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | RazorpayX Configuration (Vendor Payouts)
    |--------------------------------------------------------------------------
    |
    | Configuration for RazorpayX Payouts API. Used for vendor payouts.
    | Test mode credentials should be used for development.
    |
    */
    'razorpayx' => [
        'key_id' => env('RAZORPAYX_KEY_ID'),
        'key_secret' => env('RAZORPAYX_KEY_SECRET'),
        'account_number' => env('RAZORPAYX_ACCOUNT_NUMBER'),
        'webhook_secret' => env('RAZORPAYX_WEBHOOK_SECRET'),
        'mode' => env('RAZORPAYX_MODE', 'test'), // test or live
    ],

];
