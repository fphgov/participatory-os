<?php

declare(strict_types=1);

return [
    'jwt' => [
        'iss' => configParser(getenv('JWT_ISS')),
        'aud' => configParser(getenv('JWT_AUD')),
        'jti' => configParser(getenv('JWT_JTI')),
        'nbf' => (int)configParser(getenv('JWT_NBF')),
        'exp' => (int)configParser(getenv('JWT_EXP')),
        'auth' => [
            'secret'    => configParser(getenv('JWT_SECRET')),
            'algorithm' => ["HS256", "HS512", "HS384"],
        ]
    ]
];
