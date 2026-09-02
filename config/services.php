<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have a
    | conventional file to locate the relevant service credentials.
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

    // Gateway MCP server, ACTION_GATEWAY/mcp/v1 (upstream of /api/proxy;
    // Bearer key + JSON-RPC tools/call).
    'chatbot-upstream' => [
        'url' => env('UPSTREAM_URL'),
        'key' => env('UPSTREAM_API_KEY'),
    ],

    // Cloudflare Turnstile; empty secret = verification skipped at
    // /api/csrf (activate together with the frontend TURNSTILE_SITE_KEY).
    'turnstile' => [
        'secret' => env('TURNSTILE_SECRET', ''),
    ],

];
