<?php

declare(strict_types=1);

return [
    'app' => [
        'municipality'        => str_replace('"', '', getenv('APP_MUNICIPALITY')),
        'phone'               => str_replace('"', '', getenv('APP_PHONE')),
        'url'                 => str_replace('"', '', getenv('APP_URL')),
        'email'               => str_replace('"', '', getenv('APP_EMAIL')),
        'account'             => [
            'clearTimeHour' => (int)str_replace(['"', "'"], "", getenv('APP_ACCOUNT_CLEAR_TIME_HOUR')),
        ],
        'notification'        => [
            'frequency' => (int)str_replace(['"', "'"], "", getenv('APP_NOTIFICATION_FREQUENCY')),
            'mail'      => [
                'testTo'   => getenv('APP_NOTIFICATION_MAIL_TESTTO'),
                'subject'  => getenv('APP_NOTIFICATION_MAIL_SUBJECT'),
                'replayTo' => getenv('APP_NOTIFICATION_MAIL_REPLAYTO'),
            ],
            'force' => (string)getenv('APP_NOTIFICATION_FORCE') === "true",
        ],
        'pagination' => [
            'maxPageSize'        => (int)str_replace(['"', "'"], "", getenv('APP_PAGINATION_MAX_PAGE_SIZE')),
            'maxPageSizeForVote' => (int)str_replace(['"', "'"], "", getenv('APP_PAGINATION_MAX_PAGE_SIZE_FOR_VOTE')),
        ],
        'paths' => [
            'files' => getenv('APP_UPLOAD'),
        ]
    ],
];
