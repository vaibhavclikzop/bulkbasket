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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],
    'smswala' => [
        'url' => env('SMSWALA_API_URL'),
        'key' => env('SMSWALA_API_KEY'),
        'campaign' => env('SMSWALA_CAMPAIGN'),
        'routeid' => env('SMSWALA_ROUTE_ID'),
        'sender' => env('SMSWALA_SENDER'),
        'pe_id' => env('SMSWALA_PE_ID'),
        'templates' => [
            'template' => env('SMSWALA_TEMPLATE_ID'),
            'otp' => env('SMSWALA_TEMPLATE_OTP'),
            'due_alert' => env('SMSWALA_TEMPLATE_DUE_ALERT'),
        ],
    ],

    'hdfc' => [
        'base_url'    => env('HDFC_BASE_URL'),
        'merchant_id' => env('HDFC_MERCHANT_ID'),
        'client_id'   => env('PAYMENT_PAGE_CLIENT_ID'),
        'api_key'     => env('HDFC_API_KEY'),
    ],
    
    'google' => [
    'map_key' => env('GOOGLE_MAPS_API_KEY'),
],


];
