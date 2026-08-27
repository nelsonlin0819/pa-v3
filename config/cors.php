<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    'allowed_origins' => [env('FRONTEND_ORIGIN', 'https://chatbot-transaction-frontend.15064719d.workers.dev')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Csrf-Token', 'X-Turnstile-Token'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
