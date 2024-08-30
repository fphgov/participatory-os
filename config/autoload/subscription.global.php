<?php

declare(strict_types=1);

return [
    'subscription' => [
        'newsletterApi' => getenv('NEWSLETTER_API'),
        'subscribeCid' => getenv('SUBSCRIBE_CID'),
    ],
];
