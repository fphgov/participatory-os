<?php

declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

const ALLOWED_HEADERS = [
    'Origin',
    'X-Requested-With',
    'Content-Type',
    'Accept',
    'Authorization'
];

$corsOrigins = explode(',', getenv('CORS_ORIGIN') ?: '');

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => $corsOrigins ?: [],
        'allowed_headers' => [...ALLOWED_HEADERS],
        'allowed_max_age' => '3600',
        'credentials_allowed' => false,
        'exposed_headers' => [],
    ],
];
