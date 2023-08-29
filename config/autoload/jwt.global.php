<?php

declare(strict_types=1);

return [
    'jwt' => [
        'iss' => getenv('JWT_ISS'),
        'aud' => getenv('JWT_AUD'),
        'jti' => getenv('JWT_JTI'),
        'nbf' => (int)str_replace(['"', "'"], "", getenv('JWT_NBF')),
        'exp' => (int)str_replace(['"', "'"], "", getenv('JWT_EXP')),
        'auth' => [
            'secret' => getenv('JWT_SECRET'),
        ]
    ]
];
