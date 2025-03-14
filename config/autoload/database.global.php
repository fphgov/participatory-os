<?php

declare(strict_types=1);

return [
    'db' => [
        'driver'   => configParser(getenv('DB_DRIVER')),
        'hostname' => configParser(getenv('DB_HOSTNAME')),
        'port'     => (int)configParser(getenv('DB_PORT')),
        'database' => configParser(getenv('DB_DATABASE')),
        'user'     => configParser(getenv('DB_USER')),
        'password' => configParser(getenv('DB_PASSWORD')),
        'charset'  => configParser(getenv('DB_CHARSET')),
        'options' => [
            'buffer_results' => true,
        ],
    ]
];
