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

    'pakasir' => [
        'base_url' => env('PAKASIR_BASE_URL', 'https://pakasir.zone.id'),
        // Platform-level credentials — used to charge owners for SaaS subscription
        'platform_project' => env('PAKASIR_PLATFORM_PROJECT'),
        'platform_api_key' => env('PAKASIR_PLATFORM_API_KEY'),
        'platform_signature' => env('PAKASIR_PLATFORM_SIGNATURE'),
    ],

    'app_settings' => [
        // Trial duration in days (defaults to 3)
        'trial_days' => (int) env('TRIAL_DAYS', 3),
    ],

];
