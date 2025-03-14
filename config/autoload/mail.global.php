<?php

declare(strict_types=1);

use Laminas\Mail\Address;

return [
    'mail'             => [
        'smtp'     => [
            'name'              => configParser(getenv('SMTP_NAME')),
            'host'              => configParser(getenv('SMTP_HOST')),
            'port'              => (int)configParser(getenv('SMTP_PORT')),
            'connection_class'  => configParser(getenv('SMTP_CONNECTION_CLASS')),
            'connection_config' => [
                'username'       => configParser(getenv('SMTP_CONNECTION_CONFIG_USERNAME')),
                'password'       => configParser(getenv('SMTP_CONNECTION_CONFIG_PASSWORD')),
                'ssl'            => configParser(getenv('SMTP_CONNECTION_CONFIG_SSL')),
                'novalidatecert' => (bool)configParser(getenv('SMTP_CONNECTION_CONFIG_DISABLE_CHECK_CERT')),
            ],
        ],
        'defaults' => [
            'addFrom'     => new Address(configParser(getenv('SMTP_DEFAULTS_ADD_FROM')), configParser(getenv('SMTP_DEFAULTS_ADD_FROM_NAME'))),
            'setEncoding' => 'UTF-8',
        ],
        'headers' => [
            'message_id_domain' => configParser(getenv('SMTP_HEADERS_MESSAGE_ID_DOMAIN')),
        ]
    ],
];
