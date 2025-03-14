<?php

declare(strict_types=1);

return [
    'newsletter' => [
        'url' => configParser(getenv('NEWSLETTER_API_URL')),
        'cid' => configParser(getenv('NEWSLETTER_API_CID')),
    ],
];
