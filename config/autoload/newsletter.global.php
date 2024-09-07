<?php

declare(strict_types=1);

return [
    'newsletter' => [
        'url' => str_replace('"', '', getenv('NEWSLETTER_API_URL')),
        'cid' => str_replace('"', '', getenv('NEWSLETTER_API_CID')),
    ],
];
