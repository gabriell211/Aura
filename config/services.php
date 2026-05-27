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

    'printwayy' => [
        'webhook_token' => env('PRINTWAYY_WEBHOOK_TOKEN'),
        'api_base_url' => env('PRINTWAYY_API_BASE_URL', 'https://api.printwayy.com/devices/v1'),
        'api_token' => env('PRINTWAYY_API_TOKEN'),
        'workspace_id' => env('PRINTWAYY_WORKSPACE_ID'),
        'equipment_endpoint' => env('PRINTWAYY_EQUIPMENT_ENDPOINT', '/printers'),
        'counters_endpoint' => env('PRINTWAYY_COUNTERS_ENDPOINT', '/printers/{printer_id}/counters'),
        'alerts_endpoint' => env('PRINTWAYY_ALERTS_ENDPOINT', '/alerts'),
        'tickets_endpoint' => env('PRINTWAYY_TICKETS_ENDPOINT', ''),
        'verify_ssl' => env('PRINTWAYY_VERIFY_SSL', true),
        'timeout' => (int) env('PRINTWAYY_TIMEOUT', 15),
        'retries' => (int) env('PRINTWAYY_RETRIES', 3),
        'retry_sleep_ms' => (int) env('PRINTWAYY_RETRY_SLEEP_MS', 250),
    ],

    'infinitepay' => [
        'base_url' => env('INFINITEPAY_BASE_URL', 'https://api.checkout.infinitepay.io'),
        'handle' => env('INFINITEPAY_HANDLE'),
        'timeout' => (int) env('INFINITEPAY_TIMEOUT', 15),
        'redirect_url' => env('INFINITEPAY_REDIRECT_URL'),
        'webhook_url' => env('INFINITEPAY_WEBHOOK_URL'),
        'checkout_urls' => [
            'start' => env('INFINITEPAY_CHECKOUT_URL_START'),
            'pro' => env('INFINITEPAY_CHECKOUT_URL_PRO'),
            'enterprise' => env('INFINITEPAY_CHECKOUT_URL_ENTERPRISE'),
        ],
        'fallback_checkout_url' => env('INFINITEPAY_FALLBACK_CHECKOUT_URL'),
    ],

];
