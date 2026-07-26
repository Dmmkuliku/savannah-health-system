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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'nhif' => [
        'facility_code' => env('NHIF_FACILITY_CODE', 'SHS-DOD'),
        'base_url' => env('NHIF_BASE_URL', ''),
        'client_id' => env('NHIF_CLIENT_ID', ''),
        'client_secret' => env('NHIF_CLIENT_SECRET', ''),
        'mode' => env('NHIF_MODE', 'stub'),
    ],

    'gepg' => [
        'facility_code' => env('GEPG_FACILITY_CODE', 'SHS001'),
        'sp_code' => env('GEPG_SP_CODE', ''),
        'base_url' => env('GEPG_BASE_URL', ''),
        'mode' => env('GEPG_MODE', 'stub'),
    ],

];
