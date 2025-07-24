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

    'stripe' => [
        'key' => env('STRIPE_KEY', 'pk_test_51QUMErDgYV6zJ17v6MyFnaLZiK7jllm3Lsdq1OmxVbWZowVAWaoOy9CDjrde2byDyiPmaJ6xudcpPkpGsH7Oo0RU00mavjYxpt'),
        'secret' => env('STRIPE_SECRET', 'sk_test_51QUMErDgYV6zJ17vUKXcpo3yxnLhoxw1tVAuZUGcyoFt4qXazrcm7jwkXhMT2WxZau74W43eRngYmNAPrrq8sEiZ00m7PQgmXj'),
    ],

];
