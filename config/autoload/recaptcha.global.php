<?php

declare(strict_types=1);

return [
    'recaptcha' => [
        'secret' => configParser(getenv('RECAPTCHA_SECRET')),
    ],
];
