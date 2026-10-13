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

    'accesos' => [
        'base_url' => env('ACCESOS_BASE_URL', 'https://accesos.digitalneza.com'),
        'system_key' => env('ACCESOS_SYSTEM_KEY'),
        'departamentos_url' => env('ACCESOS_DEPARTAMENTOS_URL', 'https://accesos.digitalneza.com/api/departamentos'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'provider_lookup_model' => env('OPENAI_PROVIDER_LOOKUP_MODEL', 'gpt-4.1-mini'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
