<?php

declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

$corsOrigins = explode(',', getenv('CORS_ORIGIN'));

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => is_array($corsOrigins) ? $corsOrigins : [],
        'allowed_headers' => ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization'],
        'allowed_max_age' => '3600',
        'credentials_allowed' => false,
        'exposed_headers' => [],
    ],
];