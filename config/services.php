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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret' => env('RECAPTCHA_SECRET'),
        'threshold' => env('RECAPTCHA_THRESHOLD', 0.5),
        // Alerts configuration
        'alerts' => [
            'email' => env('RECAPTCHA_ALERT_EMAIL'),
            'slack_webhook' => env('RECAPTCHA_SLACK_WEBHOOK'),
            'threshold' => env('RECAPTCHA_ALERT_THRESHOLD', 10), // number of failures
            'window' => env('RECAPTCHA_ALERT_WINDOW', 5), // minutes
        ],
    ],

    // TMDB (The Movie Database) API credentials - keep values in .env, do NOT commit secrets
    'tmdb' => [
        // v3 api key (optional fallback)
        'api_key' => env('TMDB_API_KEY'),
        // v4 read access token (preferred for server-to-server requests)
        'read_access_token' => env('TMDB_READ_ACCESS_TOKEN'),
        // base url for TMDB API
        'base_url' => env('TMDB_BASE_URL', 'https://api.themoviedb.org/3'),
    ],
];
