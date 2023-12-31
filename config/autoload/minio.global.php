<?php

declare(strict_types=1);

return [
    'minio' => [
        'endpoint'  => getenv('MINIO_ENDPOINT'),
        'useSSL'    => false,
        'accessKey' => getenv('MINIO_ROOT_USER'),
        'secretKey' => getenv('MINIO_ROOT_PASSWORD'),
    ]
];
