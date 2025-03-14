<?php

declare(strict_types=1);

return [
    'minio' => [
        'region'    => configParser(getenv('MINIO_REGION')),
        'endpoint'  => configParser(getenv('MINIO_ENDPOINT')),
        'useSSL'    => false,
        'accessKey' => configParser(getenv('MINIO_ROOT_USER')),
        'secretKey' => configParser(getenv('MINIO_ROOT_PASSWORD')),
    ]
];
