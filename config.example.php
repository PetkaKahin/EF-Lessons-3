<?php

declare(strict_types=1);

return [
    'API_TOKEN' => 'change-me',
    'DATABASE_PATH' => __DIR__ . '/var/app.sqlite',
    'MIGRATIONS_PATH' => __DIR__ . '/Infrastructure/Database/migrations',
    'WEBHOOK_URL' => 'http://nginx/webhook-receiver',
    'WEBHOOK_LOG_PATH' => __DIR__ . '/var/webhook.log',
    'TIMEZONE' => 'UTC',
    'TIME_DATABASE_FORMAT' => 'Y-m-d\TH:i:s\Z',
    'TIME_API_FORMAT' => 'Y-m-d\TH:i:s\Z',
    'TIME_LOG_FORMAT' => 'Y-m-d\TH:i:s\Z',
    'TIME_DURATION_PRECISION' => 1,
    'APP_URL' => 'http://localhost:5173',
    'DEBUG' => true,
];
